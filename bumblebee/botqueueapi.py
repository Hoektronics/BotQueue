import urlparse
import oauth2 as oauth
import json

class BotQueueAPI():
  
  authorize_url = 'http://www.botqueue.com/app/authorize'
  endpoint_url = 'http://www.botqueue.com/api/v1/endpoint'
  
  #todo: 2 constructors, or a separate call to setToken()?
  def __init__(self, consumer_key, consumer_secret):
    self.consumer = oauth.Consumer(consumer_key, consumer_secret)

  def setToken(self, token_key, token_secret):
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
    
    #print "-------url------"
    #print url
    #print "-------body------"
    #print body
    
    # make the call
    resp, content = self.client.request(url, "POST", body)
    try:

      #print "-------resp------"
      #print resp
      #print "-------content------"
      #print content

      if resp['status'] != '200':
        raise Exception("Invalid response %s." % resp['status'])

      result = json.loads(content)
  
    except Exception as ex:
      result = {'status' : 'error', 'error' : str(ex)}
    
    return result

  def requestToken(self):
    self.client = oauth.Client(self.consumer)

    result = self.apiCall('requesttoken')
    if result['status'] == 'success':
      self.setToken(result['data']['oauth_token'], result['data']['oauth_token_secret'])
      return result['data']
    else:
      raise Exception("Error getting token: %s" % result['error'])

  def getAuthorizeUrl(self):
    return self.authorize_url + "?oauth_token=" + self.token.key 

  def convertToken(self, verifier):
    self.token.set_verifier(verifier)
    self.client = oauth.Client(self.consumer, self.token)

    result = self.apiCall('accesstoken')
    if result['status'] == 'success':
      self.setToken(result['data']['oauth_token'], result['data']['oauth_token_secret'])
      return result['data']
    else:
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
  
  def updateJobProgress(self, job_id, progress):
    return self.apiCall('updatejobprogress', {'job_id' : job_id, 'progress' : progress})

  def jobInfo(self, job_id):
    return self.apiCall('jobinfo', {'job_id' : job_id})
  
  def listBots(self):
    return self.apiCall('listbots')
    
  def findNewJob(self, bot_id):
    return self.apiCall('findnewjob', {'bot_id' : bot_id});    