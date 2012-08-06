import urlparse
import oauth2 as oauth
import json

consumer_key = '7f16659a9d83655c88e75e28b72223ca4e059b9b'
consumer_secret = '78743f8a1c35830b80e724a87431be0c19812e3a'
consumer = oauth.Consumer(consumer_key, consumer_secret)

token_key = '86125f53cad61d22b8390d1daf52c3b563107852'
token_secret = '087c013a0b4f72cdbaa8bd1535f296690f3037b9'
token = oauth.Token(token_key, token_secret)

endpoint_url = 'http://botqueue.com/api/v1/endpoint'

client = oauth.Client(consumer, token)

resp, content = client.request(endpoint_url, "POST", 'api_call=listjobs&queue_id=1')

#print "----response-----"
#print resp
#print "----content-----"
#print content
#print

try:
  if resp['status'] != '200':
    raise Exception("Invalid response %s." % resp['status'])

  data = json.loads(content)
  
  print data
  
except Exception as ex:
  print ex