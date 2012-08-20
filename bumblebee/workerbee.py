import time
import drivers
import tempfile
import urllib2
import os
import sys
import hive
import botqueueapi
import hashlib

class WorkerBee():
  
  data = {}
  
  def __init__(self, data):

    #find our local config info.
    self.global_config = hive.config.get()
    for row in self.global_config['workers']:
      if row['name'] == data['name']:
        self.config = row

    #get various objects we'll need
    self.api = botqueueapi.BotQueueAPI()
    self.data = data
    self.driver = self.driverFactory()

    self.cacheHit = False

    #look at our current state to check for problems.
    self.startupCheckState()

    #connect to our driver.
    try:
      self.log("Connecting to driver.")
      self.driver.connect()
    except Exception as ex:
      self.log(ex);

  def startupCheckState(self):
    self.log("Bot startup")
    #we shouldn't startup in a working or completed state... that implies some sort of error.
    if (self.data['status'] == 'working' or self.data['status'] == 'finished'):
      self.log("Startup in %s mode, dropping job # %s" % (self.data['status'], self.data['job']['id']))
      result = self.api.dropJob(self.data['job']['id'])
      self.log("Dropping existing job.")
      if (result['status'] == 'success'):
        self.getOurInfo()
      else:
        raise Exception("Unable to clear stale job: %s" % result['error'])

  def log(self, message):
    print "[%s] %s: %s" % (time.strftime("%Y-%m-%d %H:%M:%S"), self.data['name'], message)

  def driverFactory(self):
    if (self.config['driver'] == 's3g'):
      import drivers.s3gdriver
      return drivers.s3gdriver(self.config);
    elif (self.config['driver'] == 'printcore'):
      import drivers.printcore
      return drivers.printcoredriver(self.config)
    elif (self.config['driver'] == 'dummy'):
      import drivers.dummydriver
      return drivers.dummydriver.dummydriver(self.config)
    else:
      raise Exception("Unknown driver specified.")
      
  #this is our entry point for the worker subprocess
  def run(self):
    while True:
      if self.data['status'] == 'idle':
        try:
          self.getNewJob()
        except Exception as ex:
          #todo: handle any errors from the driver, such as loss of comms or printer failure
          self.log(ex)
      elif self.data['status'] == 'working':
        self.processJob()
      else: #we're either error, maintenance, or offline... wait until that changes
        time.sleep(10) # sleep for a bit to not hog resources
        self.getOurInfo()
        self.log("waiting for bot to be fixed")

  #get bot info from the mothership
  def getOurInfo(self):
    self.log("Looking for new job.")
    result = self.api.getBotInfo(self.data['id'])
    if (result['status'] == 'success'):
      self.data = result['data']
    else:
      raise Exception("Error looking up our info: %s" % result['error'])
  
  #get a new job to process from the mothership  
  def getNewJob(self):
    self.log("Looking for new job.")
    result = self.api.findNewJob(self.data['id'])
    if (result['status'] == 'success'):
      if (len(result['data'])):
        job = result['data']
        jresult = self.api.grabJob(self.data['id'], job['id'])
        if (jresult['status'] == 'success'):
          self.job = jresult['data']['job']
          self.data = jresult['data']['bot']
          self.log("grabbed job %s" % self.job['name'])
        else:
          raise Exception("Error grabbing job: %s" % jresult['error'])
      else:
        time.sleep(10) #todo: make this sleep get longer with each successive try.
    else:
      raise Exception("Error finding new job: %s" % result['error'])

  #download our job and make sure its cool
  def downloadJob(self):

    #prepare our file for storage
    self.checkCacheDirectory()
    self.openJobFile(self.job['file'])

    #do we need to download it?
    if not self.cacheHit:
      #download our file.
      self.log("downloading %s to %s." % (self.job['file']['url'], self.jobFilePath))
      urlFile = self.openUrl(self.job['file']['url'])
      chunk = 4096
      md5 = hashlib.md5()
      lastUpdate = 0
      self.fileSize = 0
      while 1:
        data = urlFile.read(chunk)
        if not data:
          break
        md5.update(data)
        self.jobFile.write(data)
        self.fileSize = self.fileSize + len(data)

        latest = float(self.fileSize) / float(self.job['file']['size'])*100
        if (time.time() - lastUpdate > 15):
          self.log("download: %0.2f%%" % latest)
          lastUpdate = time.time()
          self.api.updateJobProgress(self.job['id'], "%0.5f" % latest)
    
      #check our final md5 sum.
      if md5.hexdigest() != self.job['file']['md5']:
        self.log("Downloaded file hash did not match! %s != %s" % (md5.hexdigest(), self.job['file']['md5']))
        raise Exception()
      else:
        self.log("Download complete.")
    else:
      self.log("Using cached file %s" % self.jobFilePath)

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
          self.log("Existing file found: hashes did not match! %s != %s" % (myhash, fileinfo['md5']))
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
    #try:
    self.driver.startPrint(self.jobFile, self.fileSize)
    while self.driver.isRunning():
      latest = self.driver.getPercentage()
      self.log("print: %0.2f%%" % latest)
      if (time.time() - lastUpdate > 15):
        lastUpdate = time.time()
        self.api.updateJobProgress(self.job['id'], "%0.5f" % latest)
      time.sleep(1)

    self.log("Print finished.")
  
    #finish the job online, and mark as completed.
    result = self.api.completeJob(self.job['id'])
    if result['status'] == 'success':
      self.job = result['data']['job']
      self.data = result['data']['bot']
    else:
      raise Exception("Error completing job: %s" % result['error'])
      
      #todo: switch to threaded.
      # while 1:
      #   line = self.jobFile.readline()
      #   if not line:
      #       break
      #   #print "%d: %s" % (linenum, line)
      #   self.driver.execute(line)
      #   currentPosition = currentPosition + len(line)
      #   
      #   # this will really need to happen outside our thread, so we don't interrupt printing.
      #   # Update our print status every X lines/bytes/minutes
      #   latest = float(currentPosition) / float(self.fileSize)*100
      #   if (time.time() - lastUpdate > 15):
      #     self.log("print: %0.2f%%" % latest)
      #     lastUpdate = time.time()
      #     self.api.updateJobProgress(self.job['id'], "%0.5f" % latest)
    # except Exception as ex:
    #   #todo: handle any errors from the driver, such as loss of comms or printer failure
    #   self.log(ex)
    #   raise ex
    # finally:
    self.jobFile.close()