import urlparse
import oauth2 as oauth
import json
import hive
import webbrowser
import logging
import httplib2
import socket

class NetworkError(Exception):
  pass

class BotQueueAPI():
  
  authorize_url = 'http://www.botqueue.com/app/authorize'
  endpoint_url = 'http://www.botqueue.com/api/v1/endpoint'
  
  #todo: 2 constructors, or a separate call to setToken()?
  def __init__(self):
    config = hive.config.get()
    self.log = logging.getLogger('botqueue')
    
    self.consumer = oauth.Consumer(config['app']['consumer_key'], config['app']['consumer_secret'])
    if config['app']['token_key']:
      self.setToken(config['app']['token_key'], config['app']['token_secret'])
    else:
      self.authorize()

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
    try:
      resp, content = self.client.request(url, "POST", body)
    
      #print "-------resp------"
      #print resp
      #print "-------content------"
      #print content

      if resp['status'] != '200':
        raise NetworkError("Invalid response %s." % resp['status'])

      result = json.loads(content)
    except httplib2.ServerNotFoundError as ex:
      raise NetworkError(str(ex))
    except socket.gaierror as ex:
      raise NetworkError(str(ex))
    except socket.error as ex:
      raise NetworkError(str(ex))
    except Exception as e:
      self.log.exception(ex)
      raise NetworkError(str(ex))
    
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

  def authorize(self  ):
    try:
      # Step 1: Get a request token. This is a temporary token that is used for 
      # having the user authorize an access token and to sign the request to obtain 
      # said access token.
      result = self.requestToken();

      # Step 2: Redirect to the provider. Since this is a CLI script we do not 
      # redirect. In a web application you would redirect the user to the URL
      # below.
      print
      print "Go to the following link in your browser: %s" % self.getAuthorizeUrl()
      print 
      webbrowser.open_new(self.getAuthorizeUrl())
  
      # After the user has granted access to you, the consumer, the provider will
      # redirect you to whatever URL you have told them to redirect to. You can 
      # usually define this in the oauth_callback argument as well.
      oauth_verifier = raw_input('What is the PIN? ')

      # Step 3: Once the consumer has redirected the user back to the oauth_callback
      # URL you can request the access token the user has approved. You use the 
      # request token to sign this request. After this is done you throw away the
      # request token and use the access token returned. You should store this 
      # access token somewhere safe, like a database, for future use.
      self.convertToken(oauth_verifier)
      #TODO: fix this to be a forever loop and handle errors.
    
      config = hive.config.get()
      config['app']['token_key'] = self.token.key
      config['app']['token_secret'] = self.token.secret
      hive.config.save(config)

    except Exception as ex:
      print "There was a problem authorizing the app: %s" % (ex)
      raise RuntimeError("There was a problem authorizing the app: %s" % (ex))

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
    return self.apiCall('findnewjob', {'bot_id' : bot_id})
    
  def getBotInfo(self, bot_id):
    return self.apiCall('botinfo', {'bot_id' : bot_id})
    
  def updateBotInfo(self, data):
    return self.apiCall('updatebot', data)