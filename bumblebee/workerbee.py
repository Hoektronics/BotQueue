import time
import drivers
import tempfile
import urllib2
import os
import sys
import hive
import ginsu
import botqueueapi
import hashlib
import logging
import random
import shutil

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
    
    #load up our driver
    self.initializeDriver()

    #look at our current state to check for problems.
    try:
      self.startupCheckState()
    except Exception as ex:
      self.exception(ex)
      
  def startupCheckState(self):
    self.info("Bot startup")

    #we shouldn't startup in a working state... that implies some sort of error.
    if (self.data['status'] == 'working'):
      self.errorMode("Startup in %s mode, dropping job # %s" % (self.data['status'], self.data['job']['id']))
  
  def errorMode(self, error):
    self.error("Error mode: %s" % error)
    
    #drop 'em if you got em.
    try:
      self.dropJob(error)
    except Exception as ex:
      self.exception(ex)
           
    #take the bot offline.
    self.info("Setting bot status as error.")
    result = self.api.updateBotInfo({'bot_id' : self.data['id'], 'status' : 'error', 'error_text' : error})
    if result['status'] == 'success':
      self.data = result['data']
    else:
      self.error("Error talking to mothership: %s" % result['error'])
      
    #notify the queen bee of our status.
    msg = Message('bot_update', self.data)
    self.pipe.send(msg)

  def initializeDriver(self):
    #try:
    #  if self.driver:
    #    self.driver.disconnect()
    #except Exception as ex:
    #  self.exception("Disconnecting driver: %s" % ex)
      
    try:
      self.driver = self.driverFactory()
      #self.debug("Connecting to driver.")
      #self.driver.connect()
    except Exception as ex:
      self.exception(ex) #dump a stacktrace for debugging.
      self.errorMode(ex)
      #self.driver.disconnect()

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
        
        #did we get a shutdown notice?
        if not self.running:
          break
      
        #idle mode means looking for a new job.
        # if self.data['status'] == 'idle':
        #   try:
        #     if not self.getNewJob():
        #       time.sleep(10)
        #   except Exception as ex:
        #     self.exception(ex)
        #slicing means we need to slice our job.
        if self.data['status'] == 'slicing':
          if self.data['job']['slicejob']['status'] == 'slicing' and self.global_config['can_slice']:
              self.sliceJob()
          # else:
          #   self.getOurInfo()
          #   time.sleep(10)
        #working means we need to process a job.
        elif self.data['status'] == 'working':
            self.processJob()
            #self.getOurInfo() #if there was a problem with the job, we'll find it by pulling in a new bot state and looping again.
            self.debug("Bot finished @ state %s" % self.data['status'])
        # else: #we're either waiting, error, or offline... wait until that changes
        #   self.info("Waiting in %s mode" % self.data['status'])
        #   try:
        #     self.getOurInfo() #see if our job has changed.
        #   except Exception as e:
        #     #todo: better error handling here.
        #     self.exception(e)
        #   if self.data['status'] == 'idle':
        #     self.info("Going online.");
        #   else:
        #     time.sleep(10) # sleep for a bit to not hog resources
        time.sleep(0.1) # sleep for a bit to not hog resources
    except Exception as ex:
      self.exception(ex)
      self.driver.disconnect()
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
  # def getNewJob(self):
  #   self.info("Looking for new job.")
  #   result = self.api.findNewJob(self.data['id'], self.global_config['can_slice'])
  #   if (result['status'] == 'success'):
  #     if (len(result['data'])):
  #       job = result['data']
  #       jresult = self.api.grabJob(self.data['id'], job['id'], self.global_config['can_slice'])
  #       if (jresult['status'] == 'success'):
  #         self.data = jresult['data']
  # 
  #         #notify the mothership.
  #         data = hive.Object()
  #         data.job = self.data['job']
  #         data.bot = self.data
  #         message = Message('job_start', data)
  #         self.pipe.send(message)
  # 
  #         self.info("grabbed job %s" % self.data['job']['name'])
  #         return True
  #       else:
  #         raise Exception("Error grabbing job: %s" % jresult['error'])
  #     else:
  #       self.getOurInfo() #see if our status has changed.
  #   else:
  #     raise Exception("Error finding new job: %s" % result['error'])
  #   return False

  def sliceJob(self):
    #download our slice file
    sliceFile = self.downloadFile(self.data['job']['slicejob']['input_file'])
    
    #create and run our slicer
    g = ginsu.Ginsu(sliceFile, self.data['job']['slicejob'])
    g.slice()
    
    #watch the slicing progress
    localUpdate = 0
    while g.isRunning():
      #notify the local mothership of our status.
      if (time.time() - localUpdate > 0.5):
        self.data['job']['progress'] = g.getProgress()
        msg = Message('job_update', self.data['job'])
        self.pipe.send(msg)
        localUpdate = time.time()
      time.sleep(0.1)
      
    #how did it go?
    sushi = g.sliceResult
    
    #move the file to the cache directory
    cacheDir = hive.getCacheDirectory()
    baseFilename = os.path.splitext(os.path.basename(self.data['job']['slicejob']['input_file']['name']))[0]
    md5sum = hive.md5sumfile(sushi.output_file)
    uploadFile = "%s%s-%s.gcode" % (cacheDir, md5sum, baseFilename)
    self.debug("Moved slice output to %s" % uploadFile)
    shutil.copy(sushi.output_file, uploadFile)

    #update our slice job progress and pull in our update info.
    self.info("Finished slicing, uploading results to main site.")
    result = self.api.updateSliceJob(job_id=self.data['job']['slicejob']['id'], status=sushi.status, output=sushi.output_log, errors=sushi.error_log, filename=uploadFile)
    self.data = result['data']
    
    #notify the queen bee of our status.
    msg = Message('bot_update', self.data)
    self.pipe.send(msg)
    msg = Message('job_update', self.data['job'])
    self.pipe.send(msg)
 
  def downloadFile(self, fileinfo):
    myfile = hive.URLFile(fileinfo)

    localUpdate = 0
    try:
      myfile.load()

      while myfile.getProgress() < 100:
        #notify the local mothership of our status.
        if (time.time() - localUpdate > 0.5):
          self.data['job']['progress'] = myfile.getProgress()
          msg = Message('job_update', self.data['job'])
          self.pipe.send(msg)
          localUpdate = time.time()
        time.sleep(0.1)
      #okay, we're done... send it back.
      return myfile
    except Exception as ex:
      self.exception(ex)
            
  def processJob(self):
    #go get 'em, tiger!
    self.jobFile = self.downloadFile(self.data['job']['file'])

    #notify the mothership of download completion
    self.api.downloadedJob(self.data['job']['id'])

    currentPosition = 0
    lastUpdate = time.time()
    try:
      self.driver.startPrint(self.jobFile)
      while self.driver.isRunning():
        latest = self.driver.getPercentage()
      
        #notify the mothership of our status.
        self.data['job']['progress'] = latest
        msg = Message('job_update', self.data['job'])
        self.pipe.send(msg)
      
        #check for messages like shutdown or stop job.
        self.checkMessages()
        if not self.running or self.data['status'] != 'working':
          return

        #occasionally update home base.
        if (time.time() - lastUpdate > 15):
          lastUpdate = time.time()
          self.info("print: %0.2f%%" % latest)
          self.api.updateJobProgress(self.data['job']['id'], "%0.5f" % latest)

        if self.driver.hasError():
          raise Exception(self.driver.getErrorMessage())
          
        time.sleep(0.1)

      #did our print finish while running?
      if self.running and self.data['status'] == 'working':
        self.info("Print finished.")
  
        #finish the job online, and mark as completed.
        notified = False
        while not notified:
          result = self.api.completeJob(self.data['job']['id'])
          if result['status'] == 'success':
            self.data = result['data']['bot']
            notified = True

            #notify the queen bee of our status.
            msg = Message('bot_update', self.data)
            self.pipe.send(msg)

            #notify the queen bee of our status.
            msg = Message('job_update', self.data['job'])
            self.pipe.send(msg)
          else:
            self.error("Error notifying mothership: %s" % result['error'])
            time.sleep(10)
    except Exception as ex:
      self.exception(ex)
      self.errorMode(ex)

  def pauseJob(self):
    self.driver.pause()
    self.paused = True

  def resumeJob(self):
    self.driver.resume()
    self.paused = False

  def stopJob(self):
    if self.driver and not self.driver.hasError():
      if self.driver.isRunning() or self.driver.isPaused():
        self.info("stopping driver.")
        self.driver.stop()
    
  def dropJob(self, error = False):
    self.stopJob()
    
    if len(self.data['job']) and self.data['job']['id']:
      result = self.api.dropJob(self.data['job']['id'], error)
      self.info("Dropping existing job.")
      if (result['status'] == 'success'):
        self.getOurInfo()
      else:
        raise Exception("Unable to drop job: %s" % result['error'])

  def shutdown(self):
    self.info("Shutting down.")
    if(self.data['status'] == 'working' and self.data['job']['id']):
      self.dropJob("Shutting down.")
    self.running = False

  #loop through our workers and check them all for messages
  def checkMessages(self):
    if self.pipe.poll():
      message = self.pipe.recv()
      self.handleMessage(message)

  #these are the messages we know about.
  def handleMessage(self, message):

    self.debug("Got message %s" % message.name)

    #mothership gave us new information!
    if message.name == 'updatedata':
      status = message.data['status']
      #did our status change?  if so, make sure to stop our currently running job.
      if self.data['status'] == 'working' and (status == 'idle' or status == 'offline' or status == 'error' or status == 'maintenance'):
        self.info("Stopping job.")
        self.stopJob()
      self.data = message.data

    #mothership sent us a new job!
    elif message.name == 'job_start':
      self.data = message.data.bot
      self.job = message.data.job

    #time to die, mr bond!
    elif message.name == 'shutdown':
      self.shutdown()
      pass

    # elif message.name == 'pause_job':
    #   self.pauseJob()
    # elif message.name == 'resume_job':
    #   self.resumeJob()
    # elif message.name == 'stop_job':
    #   self.stopJob()
    # elif message.name == 'drop_job':
    #   self.dropJob()
    #   pass

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