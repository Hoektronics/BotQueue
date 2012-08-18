import botqueueapi
import workerbee
import multiprocessing
import time
import hive

def loadbot(pipe, data):
  print "Loading bot %s" % data['name']
  worker = workerbee.WorkerBee(data)
  worker.run();

def main():
  
  workers = []
  config = hive.config.get()
  
  hive.debug.pprint(config)

  wb = botqueueapi.BotQueueAPI(config['app']['consumer_key'], config['app']['consumer_secret'])
  wb.setToken(config['app']['token_key'], config['app']['token_secret'])
  bots = wb.listBots()
  if (bots['status'] == 'success'):
    for row in bots['data']:
      
      if (isOurBot(row)):
        parent_conn, child_conn = multiprocessing.Pipe()
        p = multiprocessing.Process(target=loadbot, args=(child_conn,row,))
        p.start()
        link = { 'process' : p, 'pipe' : parent_conn }
        workers.append(link)
        time.sleep(0.5) # give us enough time to avoid contention when getting jobs.
      else:
        hive.log("Skipping bot %s" % row['name'])
  else:
    print "Bot list failure: %s" % bots['error']

  for link in workers:
    link['process'].join()

def isOurBot(bot):
  config = hive.config.get()

  for row in config['workers']:
    if bot['name'] == row['name']:
      return True
      
  return False

if __name__ == '__main__':
  main()