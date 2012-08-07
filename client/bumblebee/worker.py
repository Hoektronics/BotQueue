import urlparse
import oauth2 as oauth
import json

class WorkerBee():
  
  #request_token_url = 'http://botqueue.com/api/v1/request_token'
  #access_token_url = 'http://botqueue.com/api/v1/access_token'
  #authorize_url = 'http://botqueue.com/api/v1/authorize'
  endpoint_url = 'http://botqueue.com/api/v1/endpoint'
  
  #todo: 2 constructors, or a separate call to setToken()?
  def __init__(self, consumer_key, consumer_secret, token_key, token_secret):
    self.consumer = oauth.Consumer(consumer_key, consumer_secret)
    self.token = oauth.Token(token_key, token_secret)
    self.client = oauth.Client(self.consumer, self.token)

  def apiCall(self, call, parameters = {}, url = False, method = "POST"):
    #what url to use?
    if (url == False):
        url = self.endpoint_url

    #format our api call data.  todo: need to sanitize w/ html entities?
    body = "api_call=%s&api_output=json" % (call)
    for k, v in parameters.iteritems():
      body = body + "&%s=%s" % (k, v)
    
    # make the call
    resp, content = self.client.request(url, "POST", body)
    try:
      if resp['status'] != '200':
        raise Exception("Invalid response %s." % resp['status'])

      result = json.loads(content)
  
    except Exception as ex:
      result = {'status' : 'error', 'error' : str(ex)}
    
    return result

  def requestToken(self):
    self.client = oauth.Client(self.consumer)

    result = self.apiCall('request_token')
    if result['status'] == 'success':
      self.token = oauth.Token(result['data']['oauth_token'], result['data']['oauth_token_secret'])
      return result['data']
    else
      raise Exception("Error getting token: %s" % result['error'])

  def getAuthorizeUrl(self):
    return self.authorize_url + "?oauth_token=" + self.token.key 

  def convertToken(self, verifier):
    self.token.set_verifier(verifier)
    self.client = oauth.Client(self.consumer, self.token)

    result = self.apiCall('access_token')
    if result['status'] == 'success':
      self.token = oauth.Token(result['data']['oauth_token'], result['data']['oauth_token_secret'])
      self.client = oauth.Client(self.consumer, self.token)
      return result['data']
    else
      raise Exception("Error converting token: %s" % result['error'])

  def listQueues(self):
    return self.apiCall('listqueues')
    
  def listJobs(self, queue_id):
    return self.apiCall('listjobs', {'queue_id' : queue_id});
    
  def grabJob(self, bot_id, job_id):
    return self.apiCall('grabjob', {'bot_id' : bot_id, 'job_id' : job_id})

  def dropJob(self, job_id):
    return self.apiCall('dropjob', {'job_id' : job_id})

  def cancelJob(self, job_id):
    return self.apiCall('canceljob', {'job_id' : job_id})

  def failJob(self, job_id):
    return self.apiCall('failjob', {'job_id' : job_id})
    
  def completeJob(self, job_id):
    return self.apiCall('completejob', {'job_id' : job_id})
  
  def updateJob(self, job_id, percent):
    return self.apiCall('updatejobprogress', {'job_id' : job_id, 'percent' : percent})

  def jobInfo(self, job_id):
    return self.apiCall('jobinfo', {'job_id' : job_id})

#instantiate our worker bee to pull some data
wb = WorkerBee(consumer_key, consumer_secret, token_key, token_secret);

#pull all our queues and list all our jobs.
queues = wb.listQueues()
if queues['status'] == 'success':
  print "Get Queues: ok"
  for queue in queues['data']:
    print "#%d: %s" % (int(queue['id']), queue['name'])
    jobs = wb.listJobs(queue['id'])
    if jobs['status'] == 'success':
      if (len(jobs['data'])):
        print "\tJob Id, Name, Status"
        for job in jobs['data']:
          print "\t%d, %s, %s, %s" % (int(job['id']), job['name'], job['status'], job['file'])
    else:
      print "\tget jobs failed."
else:
  print "Get Queues: fail"
