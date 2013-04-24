import time
import drivers
import tempfile
import urllib2
import os
import subprocess
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
      self.changeStatus(result['data'])
    else:
      self.error("Error talking to mothership: %s" % result['error'])

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

    lastWebcamUpdate = time.time()
    try:
      #okay, we're off!
      self.running = True
      while self.running:
        
        #see if there are any messages from the motherbee
        self.checkMessages()
        
        #did we get a shutdown notice?
        if not self.running:
          break
      
        #slicing means we need to slice our job.
        if self.data['status'] == 'slicing':
          if self.data['job']['slicejob']['status'] == 'slicing' and self.global_config['can_slice']:
              self.sliceJob()
        #working means we need to process a job.
        elif self.data['status'] == 'working':
            self.processJob()
            #self.getOurInfo() #if there was a problem with the job, we'll find it by pulling in a new bot state and looping again.
            self.debug("Bot finished @ state %s" % self.data['status'])

        #upload a webcam pic every so often.
        if time.time() - lastWebcamUpdate > 60:
          if self.takePicture():
            self.api.webcamUpdate("webcam.jpg", bot_id = self.data['id'])
            lastWebcamUpdate = time.time()
          
        time.sleep(0.1) # sleep for a bit to not hog resources
    except Exception as ex:
      self.exception(ex)
      self.driver.stop()
      raise ex

    self.debug("Exiting.")

  #get bot info from the mothership
  def getOurInfo(self):
    self.debug("Looking up bot #%s." % self.data['id'])
    
    result = self.api.getBotInfo(self.data['id'])
    if (result['status'] == 'success'):
      self.changeStatus(result['data'])
    else:
      self.error("Error looking up bot info: %s" % result['error'])
      raise Exception("Error looking up bot info: %s" % result['error'])

  #get bot info from the mothership
  def getJobInfo(self):
    self.debug("Looking up job #%s." % self.data['job']['id'])
    result = self.api.jobInfo(self.data['job']['id'])
    if (result['status'] == 'success'):
      self.data['job'] = result['data']
    else:
      self.error("Error looking up job info: %s" % result['error'])
      raise Exception("Error looking up job info: %s" % result['error'])

  def sliceJob(self):
    #download our slice file
    sliceFile = self.downloadFile(self.data['job']['slicejob']['input_file'])
    
    #create and run our slicer
    g = ginsu.Ginsu(sliceFile, self.data['job']['slicejob'])
    g.slice()
    
    #watch the slicing progress
    localUpdate = 0
    lastUpdate = 0
    while g.isRunning():
      #check for messages like shutdown or stop job.
      self.checkMessages()
      if not self.running or self.data['status'] != 'slicing':
        self.debug("Stopping slice job")
        g.stop()
        return
      
      #notify the local mothership of our status.
      if (time.time() - localUpdate > 0.5):
        self.data['job']['progress'] = g.getProgress()
        self.sendMessage('job_update', self.data['job'])
        localUpdate = time.time()
        
      #occasionally update home base.
      if (time.time() - lastUpdate > 15):
        lastUpdate = time.time()
        self.api.updateJobProgress(self.data['job']['id'], "%0.5f" % g.getProgress())
        
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

    #hack because the upload takes forever and mothership probably has an old status.
    self.checkMessages()

    #now pull in our new data.
    self.changeStatus(result['data'])
    
    #notify the queen bee of our status.
    self.sendMessage('job_update', self.data['job'])
 
  def downloadFile(self, fileinfo):
    myfile = hive.URLFile(fileinfo)

    localUpdate = 0
    try:
      myfile.load()

      while myfile.getProgress() < 100:
        #notify the local mothership of our status.
        if (time.time() - localUpdate > 0.5):
          self.data['job']['progress'] = myfile.getProgress()
          self.sendMessage('job_update', self.data['job'])
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
    localUpdate = 0
    lastUpdate = 0
    lastTemp = 0
    try:
      self.driver.startPrint(self.jobFile)
      while self.driver.isRunning():
        latest = self.driver.getPercentage()

        #look up our temps?
        if (time.time() - lastTemp > 1):
          lastTemp = time.time()
          temps = self.driver.getTemperature()
      
        #notify the mothership of our status.
        if (time.time() - localUpdate > 0.5):
          localUpdate = time.time()
          self.data['job']['progress'] = latest
          self.data['job']['temperature'] = temps
          self.sendMessage('job_update', self.data['job'])
      
        #check for messages like shutdown or stop job.
        self.checkMessages()
        
        #did we get paused?
        while self.data['status'] == 'paused':
          self.checkMessages()
          time.sleep(0.1)

        #should we bail out of here?
        if not self.running or self.data['status'] != 'working':
          self.stopJob()
          return

        #occasionally update home base.
        if (time.time() - lastUpdate > 15):
          lastUpdate = time.time()
          self.updateHomeBase(latest, temps)
          
        if self.driver.hasError():
          raise Exception(self.driver.getErrorMessage())
          
        time.sleep(0.1)

      #did our print finish while running?
      if self.running and self.data['status'] == 'working':
        self.info("Print finished.")
  
        #send up a final 100% info.
        self.data['job']['progress'] = 100.0
        self.updateHomeBase(latest, temps)
  
        #finish the job online, and mark as completed.
        result = self.api.completeJob(self.data['job']['id'])
        if result['status'] == 'success':
          self.changeStatus(result['data']['bot'])

          #notify the queen bee of our status.
          self.sendMessage('job_update', self.data['job'])
        else:
          self.error("Error notifying mothership: %s" % result['error'])
    except Exception as ex:
      self.exception(ex)
      self.errorMode(ex)

  def pauseJob(self):
    self.info("Pausing job.")
    self.driver.pause()

  def resumeJob(self):
    self.info("Resuming job.")
    self.driver.resume()

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
    
  def changeStatus(self, data):
    #check for message sending first because if we get stale info, it might cause issues with our new state.
    self.checkMessages()
    self.sendMessage('bot_update', data)
    self.data = data
      
  def sendMessage(self, name, data = False):
    self.checkMessages()
    msg = Message(name, data)
    self.pipe.send(msg)
    
  #loop through our workers and check them all for messages
  def checkMessages(self):
    while self.pipe.poll():
      message = self.pipe.recv()
      self.handleMessage(message)

  #these are the messages we know about.
  def handleMessage(self, message):

    #self.debug("Got message %s" % message.name)

    #mothership gave us new information!
    if message.name == 'updatedata':
      if message.data['status'] != self.data['status']:
        self.info("Changing status from %s to %s" % (self.data['status'], message.data['status']))

        #okay, are we transitioning from paused to unpaused?
        if message.data['status'] == 'paused':
          self.pauseJob()
        if self.data['status'] == 'paused' and message.data['status'] == 'working':
          self.resumeJob()

      status = message.data['status']

      #did our status change?  if so, make sure to stop our currently running job.
      if (self.data['status'] == 'working' or self.data['status'] == 'paused') and (status == 'idle' or status == 'offline' or status == 'error' or status == 'maintenance'):
        self.info("Stopping job.")
        self.stopJob()
      self.data = message.data
    #time to die, mr bond!
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
 
  def updateHomeBase(self, latest, temps):
    self.info("print: %0.2f%%" % float(latest))
    if self.takePicture():
      self.api.webcamUpdate("webcam.jpg", job_id = self.data['job']['id'], progress = "%0.5f" % float(latest), temps = temps)
    else:
      self.api.updateJobProgress(self.data['job']['id'], "%0.5f" % float(latest), temps)

  def takePicture(self):
    #create our command to do the webcam image grabbing
    try:
      #do we even have a webcam config setup?
      if 'webcam' in self.config:
        #what os are we using
        myos = hive.determineOS()
        if myos == "osx":
          command = "./imagesnap -q -d '%s' webcam.jpg && sips --resampleWidth 640 --padToHeightWidth 480 640 --padColor FFFFFF -s formatOptions 60%% webcam.jpg 2>/dev/null" % (
            self.config['webcam']['device']
          )
        elif myos == "raspberrypi" or os == "linux":
          if self.data['status'] == 'working':
            watermark = "%s :: %0.2f%% :: BotQueue.com" % (self.config['name'], float(self.data['job']['progress']))
          else:
            watermark = "%s :: BotQueue.com" % self.config['name']
          command = "exec /usr/bin/fswebcam -q --jpeg 60 -d %s -r 640x480 --title '%s' webcam.jpg" % (
            self.config['webcam']['device'],
            watermark
          )
        else:
          raise Exception("Webcams are not supported on your OS.")
              
        self.info("Webcam Command: %s" % command)

        outputLog = ""
        errorLog = ""
      
        # this starts our thread to slice the model into gcode
        self.p = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
        self.info("Webcam Capture started.")
        while self.p.poll() is None:
          output = self.p.stdout.readline()
          if output:
            self.info("Webcam: %s" % output.strip())
            outputLog = outputLog + output
                        
          time.sleep(0.1)
        
        #get any last lines of output
        output = self.p.stdout.readline()
        while output:
          self.debug("Webcam: %s" % output.strip())
          outputLog = outputLog + output
          output = self.p.stdout.readline()

        #get our errors (if any)
        error = self.p.stderr.readline()
        while error:
          self.error("Webcam: %s" % error.strip())
          errorLog = errorLog + error
          error = self.p.stderr.readline()

        self.info("Webcam: capture complete.")

        #did we get errors?
        if (errorLog or self.p.returncode > 0):
          self.error("Errors detected.  Bailing.")
          return False
        else:
          return True
    #main try/catch block  
    except Exception as ex:
      self.exception(ex)
      return False
    
class Message():
  def __init__(self, name, data = None):
    self.name = name
    self.data = data
