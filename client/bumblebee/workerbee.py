class WorkerBee():
  
  data = {}
  
  def __init__(self, api, config, data):
    self.api = api
    self.config = config
    self.data = data
    self.driver = self.driverFactory()
    self.startup()

  def startup(self):
    #we shouldn't startup in a working or completed state... that implies some sort of error.
    if (self.data['status'] == 'working' or self.data['status'] == 'finished'):
      result = self.api.cancelJob(self.data['job_id'])
      if (result['status'] == 'success'):
          self.job = result['data']['job']
          self.data = result['data']['bot']
      else:
        raise Exception("Unable to clear stale job.")

    #connect to our driver.
    try:
      driver.connect()
    except Exception as ex:
      print ex;

  def driverFactory(self, config):
    if (self.config['driver'] == 's3g'):
      return BQS3GDriver(self.config);
    elif (self.config['driver'] == 'passthru'):
      return BQSerialPassthruDriver(self.config)
    elif (self.config['driver'] == 'dummy'):
      return BQDummyDriver(self.config)
    else:
      raise Exception("Unknown driver specified.")
      
  def run(self):
    #todo: threading and crap here.
    while True:
      if self.data['status'] == 'idle':
        self.getNewJob()
      elif self.data['status'] == 'working':
        self.processJob()
      elif self.data['status'] == 'finished':
        #where do we handle job finished mode?
        sleep(1)
      else: #we're either error, maintenance, or offline... wait until that changes
        sleep(1) # sleep for a second to not hog resources
      
  def getNewJob(self):
    result = api.listJobs(self.data['queue_id'])
    if (result['status'] == 'success'):
      if (len(result['data'])):
        job = result['data'][0]
        jresult = api.grabJob(self.data['id'], job['id'])
        if (jresult['status'] == 'success'):
          self.job = jresult['data']['job']
          self.data = jresult['data']['bot']
        else:
          raise Exception("Error grabbing job.")
      else:
        sleep(10) #todo: make this sleep get longer with each successive try.
    else:
      raise Exception("Error listing jobs in queue.")

  def downloadJob():
    self.local_job_file = '/tmp/file/path' #todo: dynamically generate this file.
    #download the file from the url to our local job file.
    #open a python file descriptor and save it to our object
    #don't forget to check the sha1 hash.
      
  def processJob():
    self.downloadJob()

    # is this the best way to open a big file for reading?
    with open(self.local_job_file,'r') as lines:
      for current_line, line in enumerate(lines):
        try:
          self.driver.execute(code_string)
          # Update our print status every X lines/bytes/minutes
        except Exception as ex:
          #todo: handle any errors from the driver, such as loss of comms or printer failure
          print ex
      
    #delete the job file
    #todo: v2 add caching for repeat jobs.

    #finish the job online, and mark as completed.
    result = self.api.completeJob(self.job['id'])
    if result['status'] == 'success':
      self.job = result['status']['job']
      self.data = result['status']['bot']
