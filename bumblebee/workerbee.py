import time
import drivers
import tempfile
import urllib2
import os
import sys
import hive
import botqueueapi
import hashlib
import logging
import random

class WorkerBee():
  
  data = {}
  
  def __init__(self, data, pipe):

    #find our local config info.
    self.global_config = hive.config.get()
    for row in self.global_config['workers']:
      if row['name'] == data['name']:
        self.config = row
        
    #communications with our mother bee!
    self.pipe = pipe

    #we need logging!
    self.log = logging.getLogger('botqueue')

    #get various objects we'll need
    self.api = botqueueapi.BotQueueAPI()
    self.data = data
    
    self.driver = False
    self.cacheHit = False
    self.running = False

    #look at our current state to check for problems.
    self.startupCheckState()

  def startupCheckState(self):
    self.info("Bot startup")
    #connect to our driver on startup if we're idle
    if (self.data['status'] == 'idle'):
      self.initializeDriver()
    #we shouldn't startup in a working state... that implies some sort of error.
    if (self.data['status'] == 'working'):
      self.errorMode("Startup in %s mode, dropping job # %s" % (self.data['status'], self.data['job']['id']))
  
  def errorMode(self, message):
    self.error("Error mode: %s" % message)
    
    #drop 'em if you got em.
    try:
      self.dropJob()
    except Exception as ex:
      self.exception(ex)
           
    #take the bot offline.
    self.info("Setting bot status as error.")
    result = self.api.updateBotInfo({'bot_id' : self.data['id'], 'status' : 'error', 'error_text' : message})
    if result['status'] == 'success':
      self.data = result['data']
    else:
      self.error("Error talking to mothership: %s" % result['error'])
      
    #notify the queen bee of our status.
    msg = Message('bot_update', self.data)
    self.pipe.send(msg)

  def initializeDriver(self):
    try:
      if self.driver:
        self.driver.disconnect()
    except Exception as ex:
      self.exception("Disconnecting driver: %s" % ex)
      
    try:
      self.driver = self.driverFactory()
      self.debug("Connecting to driver.")
      self.driver.connect()
    except Exception as ex:
      self.errorMode(ex)
      self.driver.disconnect()

  def driverFactory(self):
    if (self.config['driver'] == 's3g'):
      import drivers.s3gdriver
      return drivers.s3gdriver.s3gdriver(self.config);
    elif (self.config['driver'] == 'printcore'):
      import drivers.printcoredriver
      return drivers.printcoredriver.printcoredriver(self.config)
    elif (self.config['driver'] == 'dummy'):
      import drivers.dummydriver
      return drivers.dummydriver.dummydriver(self.config)
    else:
      raise Exception("Unknown driver specified.")
      
  #this is our entry point for the worker subprocess
  def run(self):
    #sleep for a random time to avoid contention
    time.sleep(random.random())

    try:
      #okay, we're off!
      self.running = True
      while self.running:
        #see if there are any messages from the motherbee
        self.checkMessages()
        if not self.running: #did we get a shutdown notice?
          break
      
        #idle mode means looking for a new job.
        if self.data['status'] == 'idle':
          try:
            self.getNewJob()
            time.sleep(10) #todo: make this sleep get longer with each successive try.
          except botqueueapi.NetworkError as e:
            self.warning("Internet down: %s" % e)
            time.sleep(10)
          except Exception as ex:
            self.exception(ex)
        elif self.data['status'] == 'working':
          #okay, we're in work mode... handle our job.
          self.processJob()

          #if there was a problem with the job, we'll find it by pulling in a new bot state and looping again.
          self.getOurInfo()
          self.debug("Bot finished @ state %s" % self.data['status'])
        else: #we're either waiting, error, or offline... wait until that changes
          self.info("Waiting in %s mode" % self.data['status'])
          try:
            self.getOurInfo() #see if our job has changed.
          except botqueueapi.NetworkError as e:
            self.warning("Internet down: %s" % e)
            time.sleep(10)
          except Exception as e:
            #todo: better error handling here.
            self.exception(e)
          if self.data['status'] == 'idle':
            self.info("Going online.");
            self.initializeDriver()
          else:
            time.sleep(10) # sleep for a bit to not hog resources
    except Exception as ex:
      self.exception(ex)
      raise ex

    self.debug("Exiting.")

  #get bot info from the mothership
  def getOurInfo(self):
    self.debug("Looking up bot #%s." % self.data['id'])
    result = self.api.getBotInfo(self.data['id'])
    if (result['status'] == 'success'):
      self.data = result['data']
    else:
      self.error("Error looking up bot info: %s" % result['error'])
      raise Exception("Error looking up bot info: %s" % result['error'])

    #notify the mothership of our status.
    msg = Message('bot_update', self.data)
    self.pipe.send(msg)

  #get bot info from the mothership
  def getJobInfo(self):
    self.debug("Looking up job #%s." % self.data['job']['id'])
    result = self.api.jobInfo(self.data['job']['id'])
    if (result['status'] == 'success'):
      self.data['job'] = result['data']
    else:
      self.error("Error looking up job info: %s" % result['error'])
      raise Exception("Error looking up job info: %s" % result['error'])
 
  #get a new job to process from the mothership  
  def getNewJob(self):
    self.info("Looking for new job.")
    result = self.api.findNewJob(self.data['id'])
    if (result['status'] == 'success'):
      if (len(result['data'])):
        job = result['data']
        jresult = self.api.grabJob(self.data['id'], job['id'])
        if (jresult['status'] == 'success'):
          self.data = jresult['data']['bot']

          #notify the mothership.
          data = hive.Object()
          data.job = self.data['job']
          data.bot = self.data
          message = Message('job_start', data)
          self.pipe.send(message)

          self.info("grabbed job %s" % self.data['job']['name'])
        else:
          raise Exception("Error grabbing job: %s" % jresult['error'])
      else:
        self.getOurInfo() #see if our status has changed.
    else:
      raise Exception("Error finding new job: %s" % result['error'])

  #download our job and make sure its cool
  def downloadJob(self):
    #prepare our file for storage
    self.checkCacheDirectory()
    self.openJobFile(self.data['job']['file'])

    #do we need to download it?
    if not self.cacheHit:
      #todo: do an actual API call.
      self.data['job']['status'] = 'downloading'

      #download our file.
      self.info("downloading %s to %s." % (self.data['job']['file']['url'], self.jobFilePath))
      urlFile = self.openUrl(self.data['job']['file']['url'])
      chunk = 4096
      md5 = hashlib.md5()
      lastUpdate = 0
      localUpdate = 0
      self.fileSize = 0
      while 1:
        data = urlFile.read(chunk)
        if not data:
          break
        md5.update(data)
        self.jobFile.write(data)
        self.fileSize = self.fileSize + len(data)

        latest = float(self.fileSize) / float(self.data['job']['file']['size'])*100

        #notify the mothership of our status.
        if (time.time() - localUpdate > 0.5):
          self.data['job']['progress'] = latest
          msg = Message('job_update', self.data['job'])
          self.pipe.send(msg)

        #notify the remote server of our job progress.
        if (time.time() - lastUpdate > 15):
          self.debug("download: %0.2f%%" % latest)
          lastUpdate = time.time()
          self.api.updateJobProgress(self.data['job']['id'], "%0.5f" % latest)
    
      #check our final md5 sum.
      if md5.hexdigest() != self.data['job']['file']['md5']:
        self.error("Downloaded file hash did not match! %s != %s" % (md5.hexdigest(), self.data['job']['file']['md5']))
        raise Exception()
      else:
        self.data['job']['status'] = 'taken'
        self.info("Download complete.")
    else:
      self.info("Using cached file %s" % self.jobFilePath)

    #notify the mothership of download completion
    self.api.downloadedJob(self.data['job']['id'])

    #reset to the beginning.
    self.jobFile.seek(0)
 
  def checkCacheDirectory(self):
    if 'cache_directory' in self.global_config:
      self.dirname = self.global_config['cache_directory']
    else:
      self.dirname = "./cache/"

    if not os.path.isdir(self.dirname):
      os.mkdir(self.dirname)      

  def openUrl(self, url):
    request = urllib2.Request(url)
    #request.add_header('User-agent', 'Chrome XXX')
    urlfile = urllib2.urlopen(request)

    return urlfile
 
  def openJobFile(self, fileinfo):
    self.cacheHit = False
    try:
      self.jobFilePath = self.dirname + os.path.basename(fileinfo['name'])
      if os.path.exists(self.jobFilePath):
        myhash = self.md5sumfile(self.jobFilePath)
        if myhash != fileinfo['md5']:
          self.warning("Existing file found: hashes did not match! %s != %s" % (myhash, fileinfo['md5']))
          raise Exception
        else:
          self.cacheHit = True
          self.fileSize = os.path.getsize(self.jobFilePath)
          self.jobFile = open(self.jobFilePath, "r")
      else:
        self.jobFile = open(self.jobFilePath, "w+")
    except Exception as ex:
      self.jobFile = tempfile.NamedTemporaryFile()
      self.jobFilePath = self.jobFile.name

  def md5sumfile(self, filename, block_size=2**18):
    md5 = hashlib.md5()
    f = open(filename, "r")
    while True:
      data = f.read(block_size)
      if not data:
        break
      md5.update(data)
    f.close()
    return md5.hexdigest()
      
  def processJob(self):
    self.downloadJob()
    currentPosition = 0
    lastUpdate = time.time()

    try:
      self.driver.startPrint(self.jobFile, self.fileSize)
      while self.driver.isRunning():
        latest = self.driver.getPercentage()
      
        #notify the mothership of our status.
        self.data['job']['progress'] = latest
        msg = Message('job_update', self.data['job'])
        self.pipe.send(msg)
      
        #check for messages like shutdown.
        self.checkMessages()
        if not self.running:
          raise Exception("Shutting down.")

        #occasionally update home base.
        try:
          if (time.time() - lastUpdate > 15):
            lastUpdate = time.time()
            self.info("print: %0.2f%%" % latest)
            self.api.updateJobProgress(self.data['job']['id'], "%0.5f" % latest)
        except botqueueapi.NetworkError as e:
          self.warning("Internet down: %s" % e)
            
        if self.driver.hasError():
          raise Exception(self.driver.getErrorMessage())
          
        time.sleep(0.5)

      self.info("Print finished.")
  
      #finish the job online, and mark as completed.
      notified = False
      while not notified:
        try:
          result = self.api.completeJob(self.data['job']['id'])
          if result['status'] == 'success':
            self.data = result['data']['bot']
            notified = True
            #notify the queen bee
            data = hive.Object()
            data.job = self.data['job']
            data.bot = self.data
            message = Message('job_end', data)
            self.pipe.send(message)
          else:
            raise Exception("Error notifying mothership: %s" % result['error'])
        except botqueueapi.NetworkError as e:
          self.warning("Internet down: %s" % e)
          time.sleep(10)
    except Exception as ex:
      self.errorMode(ex)

  def goOnline():
    self.data['status'] = 'idle'

  def goOffline():
    self.data['status'] = 'offline'

  def pauseJob(self):
    self.driver.pause()
    self.paused = True

  def resumeJob(self):
    self.driver.resume()
    self.paused = False

  def stopJob(self):
    if self.driver and not self.driver.hasError():
      if self.driver.isRunning() or self.driver.isPaused():
        self.driver.stop()      
    
  def dropJob(self):
    self.stopJob()
    
    if len(self.data['job']) and self.data['job']['id']:
      result = self.api.dropJob(self.data['job']['id'])
      self.info("Dropping existing job.")
      if (result['status'] == 'success'):
        self.getOurInfo()
      else:
        raise Exception("Unable to drop job: %s" % result['error'])

  def shutdown(self):
    self.info("Shutting down.")
    if(self.data['status'] == 'working' and self.data['job']['id']):
      self.dropJob()
    self.running = False

  #loop through our workers and check them all for messages
  def checkMessages(self):
    if self.pipe.poll():
      message = self.pipe.recv()
      self.handleMessage(message)

  #these are the messages we know about.
  def handleMessage(self, message):
    self.log.debug("Got message %s" % message.name)
    if message.name == 'go_online':
      self.data['status'] = 'idle'
    elif message.name == 'go_offline':
      self.goOnline()
    elif message.name == 'pause_job':
      self.pauseJob()
    elif message.name == 'resume_job':
      self.resumeJob()
    elif message.name == 'stop_job':
      self.stopJob()
    elif message.name == 'drop_job':
      self.dropJob()
      pass
    elif message.name == 'shutdown':
      self.shutdown()
      pass

  def debug(self, msg):
    self.log.debug("%s: %s" % (self.config['name'], msg))

  def info(self, msg):
    self.log.info("%s: %s" % (self.config['name'], msg))

  def warning(self, msg):
    self.log.warning("%s: %s" % (self.config['name'], msg))

  def error(self, msg):
    self.log.error("%s: %s" % (self.config['name'], msg))
    
  def exception(self, msg):
    self.log.exception("%s: %s" % (self.config['name'], msg))
    
class Message():
  def __init__(self, name, data = None):
    self.name = name
    self.data = data