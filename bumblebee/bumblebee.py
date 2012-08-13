import botqueueapi
import workerbee
import multiprocessing
import time

config = {
  'consumer_key' : '4b99f7bb861ad3fab5b3d4a189c81c0b893c043f',
  'consumer_secret' : 'c917f6ade3945e1acb9645dd1d7ee5d72993c6c9',
  'token_key' : 'ecfadaa22dfa54e6fb801e44ff5c04566f2582aa',
  'token_secret' : 'b06af89a7dcc34f19c537ba68bf415c22348da91'
}

workerconfig = {
  'driver' : 'dummy'
}

def loadbot(pipe, api_config, config, data):
  print "Loading bot %s" % data['name']

  api = botqueueapi.BotQueueAPI(api_config['consumer_key'], api_config['consumer_secret'])
  api.setToken(api_config['token_key'], api_config['token_secret'])
  worker = workerbee.WorkerBee(api, config, data)
  worker.run();

workers = []

wb = botqueueapi.BotQueueAPI(config['consumer_key'], config['consumer_secret'])
wb.setToken(config['token_key'], config['token_secret'])
bots = wb.listBots()
if (bots['status'] == 'success'):
  for row in bots['data']:
    parent_conn, child_conn = multiprocessing.Pipe()
    p = multiprocessing.Process(target=loadbot, args=(child_conn,config,workerconfig,row,))
    p.start()
    link = { 'process' : p, 'pipe' : parent_conn }
    workers.append(link)
    time.sleep(0.5) # give us enough time to avoid contention when getting jobs.
else:
  print "Bot list failure: %s" % bots['error']

for link in workers:
  link['process'].join()
