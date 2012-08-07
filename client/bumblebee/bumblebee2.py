import urlparse
import oauth2 as oauth

consumer_key = '7f16659a9d83655c88e75e28b72223ca4e059b9b'
consumer_secret = '78743f8a1c35830b80e724a87431be0c19812e3a'
token_key = '86125f53cad61d22b8390d1daf52c3b563107852'
token_secret = '087c013a0b4f72cdbaa8bd1535f296690f3037b9'

wb = WorkerBee(consumer_key, consumer_secret)

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
except Exception as ex:
  print "There was a problem authorizing the app: %s" % (ex)