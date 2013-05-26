import json
import hive
import logging
import time
from oauth_hook import OAuthHook
import socket
import requests
import urlparse

class NetworkError(Exception):
  pass

class ServerError(Exception):
  pass

class BotQueueAPI():
  
  version = '0.4'
  name = 'Bumblebee'
  localip = None
  
  def __init__(self):
    self.log = logging.getLogger('botqueue')
    self.config = hive.config.get()
    self.netStatus = False

    #pull in our endpoint urls
    self.authorize_url = self.config['api']['authorize_url']
    self.endpoint_url = self.config['api']['endpoint_url']
    
    # this is helpful for raspberry pi and future websockets stuff
    self.localip = self.getLocalIPAddress()
    
    # self.consumer = oauth.Consumer(self.config['app']['consumer_key'], self.config['app']['consumer_secret'])
    if self.config['app']['token_key']:
      self.setToken(self.config['app']['token_key'], self.config['app']['token_secret'])
    else:
      self.my_oauth_hook = OAuthHook(consumer_key=self.config['app']['consumer_key'], consumer_secret=self.config['app']['consumer_secret'])
      self.authorize()

  def setToken(self, token_key, token_secret):
    self.token_key = token_key
    self.token_secret = token_secret
    self.my_oauth_hook = OAuthHook(access_token = token_key, access_token_secret = token_secret, consumer_key=self.config['app']['consumer_key'], consumer_secret=self.config['app']['consumer_secret'])
    
    
  def apiCall(self, call, parameters = {}, url = False, method = "POST", retries = 999999, filepath = None):
    #what url to use?
    if (url == False):
        url = self.endpoint_url
  
    #add in our special variables
    parameters['_client_version'] = self.version
    parameters['_client_name'] = self.name
    parameters['_uid'] = self.config['uid']
    if self.localip:
      parameters['_local_ip'] = self.localip
    parameters['api_call'] = call
    parameters['api_output'] = 'json'   

    # make the call for as long as it takes.
    while retries > 0:
      respdata = None
      result = None
      try:
        self.log.debug("Calling %s - %s (%d tries remaining)" % (url, call, retries))
        
        #load in our file baby.
        files = None
        if filepath is not None:
          files = {'file': (filepath, open(filepath, 'rb'))}
          
        #make our request now.
        request = requests.Request('POST', url, data=parameters, files=files)
        request = self.my_oauth_hook(request)
        prepared = request.prepare()
        session = requests.session()
        response = session.send(prepared)
        result = json.loads(response.content)

        #sweet, our request must have gone through.
        self.netStatus = True

        #did we get the right http response code?
        if response.status_code != 200:
          raise ServerError("Bad response code")
        
        #did the api itself return an error?
        if result['status'] == 'error':
          self.log.error("API: %s" % result['error'])
        
        #is the site database down?
        if result['status'] == 'error' and result['error'] == "Failed to connect to database!":
          raise ServerError("Database is down.")
                    
        return result
        
      #these are our known errors that typically mean the network is down.
      except (NetworkError, ServerError) as ex:
        #raise NetworkError(str(ex))
        self.log.error("Internet connection is down: %s" % ex)
        retries = retries - 1
        self.netStatus = False
        time.sleep(10)
      #unknown exceptions... get a stacktrace for debugging.
      except Exception as ex:
        self.log.error("Unknown API error: %s" % ex)
        self.log.error("response: %s" % respdata)
        self.log.error("content: %s" % result)
        self.log.exception(ex)
        retries = retries - 1
        self.netStatus = False
        time.sleep(10)
    #something bad happened.
    return False
        
  def requestToken(self):
    #make our token request call or error
    result = self.apiCall('requesttoken')

    if result['status'] == 'success':
      self.setToken(result['data']['oauth_token'], result['data']['oauth_token_secret'])
      return result['data']
    else:
      raise Exception("Error getting token: %s" % result['error'])

  def getAuthorizeUrl(self):
    return self.authorize_url + "?oauth_token=" + self.token_key 

  def convertToken(self, verifier):
    #switch our temporary auth token for our real credentials
    result = self.apiCall('accesstoken', {'oauth_verifier': verifier})
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
      #webbrowser.open_new(self.getAuthorizeUrl())
  
      authorized = False
      while not authorized:
        try:
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
          authorized = True

        except Exception as ex:
          print "Invalid authorization code, please try again."
          self.log.exception(ex);

      #record the key in our config
      self.config['app']['token_key'] = self.token_key
      self.config['app']['token_secret'] = self.token_secret
      hive.config.save(self.config)

    except Exception as ex:
      self.log.exception(ex)
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

  def webcamUpdate(self, filename, bot_id = None, job_id = None, progress = None, temps = None):
    return self.apiCall('webcamupdate', {'job_id' : job_id, 'bot_id' : bot_id, 'progress' : progress, 'temperatures' : json.dumps(temps)}, filepath=filename, retries=1)

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
    return self.apiCall('updateslicejob', {'job_id':job_id, 'status':status, 'output':output, 'errors':errors}, filepath=filename)

  def getLocalIPAddress(self):
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    s.connect(("8.8.8.8",80))
    ip = s.getsockname()[0]
    s.close()
    return ip