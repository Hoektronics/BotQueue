import urlparse
import oauth2 as oauth
import json

consumer_key = '7f16659a9d83655c88e75e28b72223ca4e059b9b'
consumer_secret = '78743f8a1c35830b80e724a87431be0c19812e3a'
token_key = '86125f53cad61d22b8390d1daf52c3b563107852'
token_secret = '087c013a0b4f72cdbaa8bd1535f296690f3037b9'

class WorkerBee():
  
  endpoint_url = 'http://botqueue.com/api/v1/endpoint'
  
  def __init__(self, consumer_key, consumer_secret, token_key, token_secret):
    self.consumer = oauth.Consumer(consumer_key, consumer_secret)
    self.token = oauth.Token(token_key, token_secret)
    self.client = oauth.Client(self.consumer, self.token)

  def apiCall(self, call, parameters = {}):
    #format our api call data.  todo: need to sanitize w/ html entities?
    body = "api_call=%s&api_output=json" % (call)
    for k, v in parameters.iteritems():
      body = body + "&%s=%s" % (k, v)
    
    # make the call
    resp, content = self.client.request(self.endpoint_url, "POST", body)
    try:
      if resp['status'] != '200':
        raise Exception("Invalid response %s." % resp['status'])

      result = json.loads(content)
  
    except Exception as ex:
      result = {'status' : 'error', 'error' : str(ex)}
    
    return result

  def listQueues(self):
    return self.apiCall('listqueues')
    
  def listJobs(self, queue_id):
    return self.apiCall('listjobs', {'queue_id' : queue_id});
    
  def grabJob(self, machine_id, job_id):
    return self.apiCall('grabjob', {'machine_id' : machine_id, 'job_id' : job_id})

  def dropJob(self, job_id):
    return self.apiCall('dropjob', {'job_id' : job_id})

  def cancelJob(self, job_id):
    return self.apiCall('canceljob', {'machine_id' : machine_id, 'job_id' : job_id})
    
  def finishJob(self, job_id):
    return self.apiCall('finishjob', {'job_id' : job_id})
  
  def updateJob(self, job_id, percent):
    return self.apiCall('updatejob', {'job_id' : job_id, 'percent' : percent})

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
