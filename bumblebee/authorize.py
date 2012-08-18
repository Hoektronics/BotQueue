#import WorkerBee
import botqueueapi

consumer_key = '4b99f7bb861ad3fab5b3d4a189c81c0b893c043f'
consumer_secret = 'c917f6ade3945e1acb9645dd1d7ee5d72993c6c9'
#token_key = '86125f53cad61d22b8390d1daf52c3b563107852'
#token_secret = '087c013a0b4f72cdbaa8bd1535f296690f3037b9'

wb = botqueueapi.BotQueueAPI(consumer_key, consumer_secret)

try:
  # Step 1: Get a request token. This is a temporary token that is used for 
  # having the user authorize an access token and to sign the request to obtain 
  # said access token.
  result = wb.requestToken();

  # Step 2: Redirect to the provider. Since this is a CLI script we do not 
  # redirect. In a web application you would redirect the user to the URL
  # below.
  print "Go to the following link in your browser: %s" % wb.getAuthorizeUrl()
  print 

  # After the user has granted access to you, the consumer, the provider will
  # redirect you to whatever URL you have told them to redirect to. You can 
  # usually define this in the oauth_callback argument as well.
  accepted = 'n'
  while accepted.lower() == 'n':
      accepted = raw_input('Have you authorized me? (y/n) ')
  oauth_verifier = raw_input('What is the PIN? ')

  # Step 3: Once the consumer has redirected the user back to the oauth_callback
  # URL you can request the access token the user has approved. You use the 
  # request token to sign this request. After this is done you throw away the
  # request token and use the access token returned. You should store this 
  # access token somewhere safe, like a database, for future use.
  wb.convertToken(oauth_verifier)

  #instantiate our worker bee to pull some data
  #wb = BotQueueAPI(consumer_key, consumer_secret, token_key, token_secret);

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
            print "\t%d, %s, %s, %s" % (int(job['id']), job['name'], job['status'], job['file']['name'])
      else:
        print "\tget jobs failed."
  else:
    print "Get Queues: fail"


except Exception as ex:
  print "There was a problem authorizing the app: %s" % (ex)
  
  
