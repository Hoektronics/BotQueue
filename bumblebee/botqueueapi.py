import urlparse
import oauth2 as oauth
import json
import hive
import webbrowser
import logging
import httplib
import httplib2
import socket
import hashlib
import time
from poster.encode import multipart_encode
from poster.streaminghttp import register_openers
import urllib2

class NetworkError(Exception):
  pass

class BotQueueAPI():
  
  version = '2.0'
  name = 'Bumblebee'
  
  def __init__(self):
    self.log = logging.getLogger('botqueue')
    self.config = hive.config.get()
    self.netStatus = False

    # Register the poster module's streaming http handlers with urllib2
    register_openers()

    #pull in our endpoint urls
    self.authorize_url = self.config['api']['authorize_url']
    self.endpoint_url = self.config['api']['endpoint_url']
    
    #pull in our user credentials, or trigger the auth process if they aren't found.
    self.consumer = oauth.Consumer(self.config['app']['consumer_key'], self.config['app']['consumer_secret'])
    if self.config['app']['token_key']:
      self.setToken(self.config['app']['token_key'], self.config['app']['token_secret'])
    else:
      self.authorize()

  def setToken(self, token_key, token_secret):
    self.token = oauth.Token(token_key, token_secret)
    self.client = oauth.Client(self.consumer, self.token, timeout=30)

  def apiCall(self, call, parameters = {}, url = False, method = "POST", retries = 999999):
    #what url to use?
    if (url == False):
        url = self.endpoint_url

    #add in our special variables
    parameters['_client_version'] = self.version
    parameters['_client_name'] = self.name
    parameters['_uid'] = self.config['uid']

    #format our api call data.  todo: need to sanitize w/ html entities?
    body = "api_call=%s&api_output=json" % (call)
    for k, v in parameters.iteritems():
      body = body + "&%s=%s" % (k, v)

    #how many times have we tried?
    tries = 0
    
    # make the call for as long as it takes.
    while retries > 0:
      resp = ""
      content = ""
      try:
        self.log.debug("Calling %s - %s (%d tries remaining)" % (url, call, retries))

        resp, content = self.client.request(url, "POST", body)

        if resp['status'] != '200':
          raise NetworkError("Invalid response %s." % resp['status'])

        result = json.loads(content)
        self.netStatus = True
        
        return result    
   
      #these are our known errors that typically mean the network is down.
      except (NetworkError, httplib2.ServerNotFoundError, httplib2.SSLHandshakeError, socket.gaierror, socket.error, httplib.BadStatusLine) as ex:
        #raise NetworkError(str(ex))
        self.log.error("Internet connection is down: %s" % ex)
        retries = retries - 1
        self.netStatus = False
        time.sleep(10)
      #unknown exceptions... get a stacktrace for debugging.
      except Exception as ex:
        self.log.error("Unknown API error: %s" % ex)
        self.log.error("response: %s" % resp)
        self.log.error("content: %s" % content)
        self.log.exception(ex)
        retries = retries - 1
        self.netStatus = False
        time.sleep(10)

    #something bad happened.
    return False

  def apiUploadCall(self, call, parameters = {}, url = False, method = "POST", filepath = None):
    #what url to use?
    if (url == False):
        url = self.endpoint_url

    #add in our special variables
    parameters['_client_version'] = self.version
    parameters['_client_name'] = self.name
    parameters['_uid'] = self.config['uid']
    parameters['api_call'] = call
    parameters['api_output'] = 'json'   

    #get our custom request object for file uploads
    req = oauth.Request.from_consumer_and_token(
      self.client.consumer,
      token=self.client.token,
      http_method="POST",
      http_url=url,
      parameters=parameters)

    #sign our request w/o any of the file variables included
    req.sign_request(oauth.SignatureMethod_HMAC_SHA1(), self.client.consumer, self.client.token)
    compiled_postdata = req.to_postdata()
    all_upload_params = urlparse.parse_qs(compiled_postdata, keep_blank_values=True)
    
    #parse_qs returns values as arrays, so convert back to strings
    for key, val in all_upload_params.iteritems():
      all_upload_params[key] = val[0]
      
    #add our file to upload and make the request
    all_upload_params['file'] = open(filepath, 'rb')
    datagen, headers = multipart_encode(all_upload_params)
    request = urllib2.Request(url, datagen, headers)
    
    # make the call
    try:
      respdata = urllib2.urlopen(request).read()
      result = json.loads(respdata)
    except urllib2.HTTPError, ex:
      self.log.warning('Received error code: %s' % ex.code)
    #these are our known errors that typically mean the network is down.
    except (httplib2.ServerNotFoundError, httplib2.SSLHandshakeError, socket.gaierror, socket.error) as ex:
      raise NetworkError(str(ex))
    #unknown exceptions... get a stacktrace for debugging.
    except Exception as ex:
      self.log.exception(ex)
      raise NetworkError(str(ex))

    return result

  def requestToken(self):
    self.client = oauth.Client(self.consumer)

    #make our token request call or error
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

    #switch our temporary auth token for our 
    result = self.apiCall('accesstoken')
    if result['status'] == 'success':
      self.setToken(result['data']['oauth_token'], result['data']['oauth_token_secret'])
      return result['data']
    else:
      raise Exception("Error converting token: %s" % result['error'])

  def authorize(self):
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
    
      #record the key in our config
      self.config['app']['token_key'] = self.token.key
      self.config['app']['token_secret'] = self.token.secret
      hive.config.save(self.config)

    except Exception as ex:
      print "There was a problem authorizing the app: %s" % (ex)
      raise RuntimeError("There was a problem authorizing the app: %s" % (ex))

  def listQueues(self):
    return self.apiCall('listqueues')
    
  def listJobs(self, queue_id):
    return self.apiCall('listjobs', {'queue_id' : queue_id});
    
  def grabJob(self, bot_id, job_id, can_slice):
    return self.apiCall('grabjob', {'bot_id' : bot_id, 'job_id' : job_id, 'can_slice' : can_slice})

  def dropJob(self, job_id, error = False):
    return self.apiCall('dropjob', {'job_id' : job_id, 'error' : error})

  def cancelJob(self, job_id):
    return self.apiCall('canceljob', {'job_id' : job_id})

  def failJob(self, job_id):
    return self.apiCall('failjob', {'job_id' : job_id})

  def createJobFromJob(self, job_id, quantity = 1, queue_id = 0):
    return self.apiCall('createjob', {'job_id' : job_id, 'queue_id' : queue_id, 'quantity': quantity})

  def createJobFromURL(self, url, quantity = 1, queue_id = 0):
    return self.apiCall('createjob', {'job_url' : url, 'queue_id' : queue_id, 'quantity': quantity})

  def createJobFromFile(self, filename, quantity = 1, queue_id = 0):
    return self.apiUploadCall('createjob', {'quantity': quantity, 'queue_id' : queue_id}, filepath=filename)
      
  def downloadedJob(self, job_id):
    return self.apiCall('downloadedjob', {'job_id' : job_id})
    
  def completeJob(self, job_id):
    return self.apiCall('completejob', {'job_id' : job_id})
  
  def updateJobProgress(self, job_id, progress, temps = {}):
    return self.apiCall('updatejobprogress', {'job_id' : job_id, 'progress' : progress, 'temperatures' : json.dumps(temps)}, retries = 1)

  def jobInfo(self, job_id):
    return self.apiCall('jobinfo', {'job_id' : job_id})
  
  def listBots(self):
    return self.apiCall('listbots', retries = 1)
    
  def findNewJob(self, bot_id, can_slice):
    return self.apiCall('findnewjob', {'bot_id' : bot_id, 'can_slice' : can_slice})
    
  def getBotInfo(self, bot_id):
    return self.apiCall('botinfo', {'bot_id' : bot_id})
    
  def updateBotInfo(self, data):
    return self.apiCall('updatebot', data)
    
  def updateSliceJob(self, job_id=0, status="", output="", errors="", filename=""):
    return self.apiUploadCall('updateslicejob', {'job_id':job_id, 'status':status, 'output':output, 'errors':errors}, filepath=filename)
