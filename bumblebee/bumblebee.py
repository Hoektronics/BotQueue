import botqueueapi
import workerbee
import multiprocessing
import time
import hive

log = hive.log.get()

def loadbot(pipe, data):
  try:
    log.info("Loading bot %s" % data['name'])
    worker = workerbee.WorkerBee(data)
    worker.run();
  except KeyboardInterrupt as e:
    log.debug("Bot %s exiting from keyboard interrupt." % data['name'])

def main():
  #load up our bots and start processing them.
  log.info("Started up, loading bot list.")
  wb = botqueueapi.BotQueueAPI()
  try:
    workers = []
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
          log.info("Skipping bot %s" % row['name'])
    else:
      log.error("Bot list failure: %s" % bots['error'])

    for link in workers:
      link['process'].join()
  except KeyboardInterrupt as e:
    pass

def isOurBot(bot):
  config = hive.config.get()

  for row in config['workers']:
    if bot['name'] == row['name']:
      return True
      
  return False

if __name__ == '__main__':
  main()