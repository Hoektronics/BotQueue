import time
import drivers
import tempfile
import urllib2
import os
import sys

class WorkerBee():
  
  data = {}
  
  def __init__(self, api, config, data):
    self.api = api
    self.config = config
    self.data = data
    self.driver = self.driverFactory(config)
    self.startup()

  def startup(self):
    print "Bot startup"
    #we shouldn't startup in a working or completed state... that implies some sort of error.
    if (self.data['status'] == 'working' or self.data['status'] == 'finished'):
      result = self.api.cancelJob(self.data['job_id'])
      print "Cancelling job."
      if (result['status'] == 'success'):
          self.job = result['data']['job']
          self.data = result['data']['bot']
      else:
        raise Exception("Unable to clear stale job.")

    #connect to our driver.
    try:
      print "Bot connecting"
      self.driver.connect()
    except Exception as ex:
      print ex;

  def driverFactory(self, config):
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
        self.getNewJob()
      elif self.data['status'] == 'working':
        self.processJob()
      else: #we're either error, maintenance, or offline... wait until that changes
        sleep(10) # sleep for a second to not hog resources
        print "waiting for job"
      
  def getNewJob(self):
    print "Getting new job."
    result = self.api.listJobs(self.data['queue_id'])
    if (result['status'] == 'success'):
      if (len(result['data'])):
        job = result['data'][0]
        #print "new job: %s" % job
        jresult = self.api.grabJob(self.data['id'], job['id'])
        if (jresult['status'] == 'success'):
          print "grabbed ok"
          self.job = jresult['data']['job']
          self.data = jresult['data']['bot']
        else:
          raise Exception("Error grabbing job: %s" % jresult['error'])
      else:
        sleep(10) #todo: make this sleep get longer with each successive try.
    else:
      raise Exception("Error listing jobs in queue.")

  def downloadJob(self):

    #load up our url and request params
    request = urllib2.Request(self.job['file'])
    #request.add_header('User-agent', 'Chrome XXX')
    urlfile = urllib2.urlopen(request)
    self.jobFile = tempfile.NamedTemporaryFile()

    #todo: don't forget to check the sha1 hash.
    chunk = 4096
    while 1:
        data = urlfile.read(chunk)
        if not data:
            print "done."
            break
        self.jobFile.write(data)
        sys.stdout.write('.')
        sys.stdout.flush()
        #print "Read %s bytes"%len(data)
    self.jobFile.seek(0)
      
  def processJob(self):
    self.downloadJob()

    # is this the best way to open a big file for reading?
    for linenum, line in enumerate(self.jobFile):
      try:
        #print "%d: %s" % (linenum, line)
        self.driver.execute(line)
        # Update our print status every X lines/bytes/minutes
      except Exception as ex:
        #todo: handle any errors from the driver, such as loss of comms or printer failure
        print ex
    
    #delete the job file
    #todo: v2 add caching for repeat jobs.
    self.jobFile.close()

    raise Exception("file deleted")

    #finish the job online, and mark as completed.
    result = self.api.completeJob(self.job['id'])
    if result['status'] == 'success':
      self.job = result['status']['job']
      self.data = result['status']['bot']
