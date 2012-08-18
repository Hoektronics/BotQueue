import time
import drivers
import tempfile
import urllib2
import os
import sys
import hive
import botqueueapi

class WorkerBee():
  
  data = {}
  
  def __init__(self, data):

    self.global_config = hive.config.get()
    for row in self.global_config['workers']:
      if row['name'] == data['name']:
        self.config = row

    self.api = botqueueapi.BotQueueAPI(self.global_config['app']['consumer_key'], self.global_config['app']['consumer_secret'])
    self.api.setToken(self.global_config['app']['token_key'], self.global_config['app']['token_secret'])
    self.data = data
    self.driver = self.driverFactory()
    self.startup()

  def startup(self):
    self.log("Bot startup")
    
    #we shouldn't startup in a working or completed state... that implies some sort of error.
    if (self.data['status'] == 'working' or self.data['status'] == 'finished'):
      print self.data['job']['id']
      result = self.api.dropJob(self.data['job']['id'])
      self.log("Dropping existing job.")
      if (result['status'] == 'success'):
        self.getOurInfo()
      else:
        raise Exception("Unable to clear stale job: %s" % result['error'])

    #connect to our driver.
    try:
      self.log("Connecting")
      self.driver.connect()
    except Exception as ex:
      self.log(ex);

  def log(self, message):
    print "%s: %s" % (self.data['name'], message)

  def driverFactory(self):
    if (self.config['driver'] == 's3g'):
      return drivers.S3GDriver(self.config);
    elif (self.config['driver'] == 'passthru'):
      return drivers.SerialPassthruDriver(self.config)
    elif (self.config['driver'] == 'dummy'):
      return drivers.DummyDriver(self.config)
    else:
      raise Exception("Unknown driver specified.")
      
  def run(self):
    #todo: threading and crap here.
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
        time.sleep(10) # sleep for a second to not hog resources
        self.getOurInfo()
        self.log("waiting for bot to be fixed")

  def getOurInfo(self):
    self.log("Looking for new job.")
    result = self.api.getBotInfo(self.data['id'])
    if (result['status'] == 'success'):
      self.data = result['data']
    else:
      raise Exception("Error looking up our info: %s" % result['error'])
      
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

  def downloadJob(self):

    #load up our url and request params
    request = urllib2.Request(self.job['file']['url'])
    #request.add_header('User-agent', 'Chrome XXX')
    urlfile = urllib2.urlopen(request)
    self.jobFile = tempfile.NamedTemporaryFile()

    self.log("downloading %s." % self.job['file']['url'])

    #todo: don't forget to check the sha1 hash.
    self.fileSize = 0
    chunk = 4096
    while 1:
        data = urlfile.read(chunk)
        if not data:
            break
        self.jobFile.write(data)
        self.fileSize = self.fileSize + len(data)
    self.jobFile.seek(0)
      
  def processJob(self):
    self.downloadJob()

    currentPosition = 0
    lastUpdate = time.time()

    #loop through all our lines.
    for linenum, line in enumerate(self.jobFile):
      try:
        #print "%d: %s" % (linenum, line)
        self.driver.execute(line)
        currentPosition = currentPosition + len(line)
        
        # this will really need to happen outside our thread, so we don't interrupt printing.
        # Update our print status every X lines/bytes/minutes
        latest = float(currentPosition) / float(self.fileSize)*100
        if (time.time() - lastUpdate > 30):
          self.log("%0.2f%%" % latest)
          lastUpdate = time.time()
          self.api.updateJobProgress(self.job['id'], "%0.5f" % latest)
      except Exception as ex:
        #todo: handle any errors from the driver, such as loss of comms or printer failure
        self.log(ex)
    self.log("Print finished.")
    
    #delete the job file
    #todo: v2 add caching for repeat jobs.
    self.jobFile.close()

    #finish the job online, and mark as completed.
    result = self.api.completeJob(self.job['id'])
    if result['status'] == 'success':
      self.job = result['data']['job']
      self.data = result['data']['bot']
    else:
      raise Exception("Error completing job: %s" % result['error'])
