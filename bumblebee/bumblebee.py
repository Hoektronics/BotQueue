import botqueueapi
import workerbee
import multiprocessing
import time
import hive

def loadbot(pipe, data):
  try:
    print "Loading bot %s" % data['name']
    worker = workerbee.WorkerBee(data)
    worker.run();
  except KeyboardInterrupt as e:
    print "Bot %s exiting from keyboard interrupt." % data['name']

def main():
  #add the drivers folder
  #addModuleFolder("drivers")
  
  #load up our bots and start processing them.
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
          hive.log("Skipping bot %s" % row['name'])
    else:
      print "Bot list failure: %s" % bots['error']

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

def addModuleFolder(folder):
  # realpath() with make your script run, even if you symlink it :)
  cmd_folder = os.path.realpath(os.path.abspath(os.path.split(inspect.getfile( inspect.currentframe() ))[0]))
  if cmd_folder not in sys.path:
     sys.path.insert(0, cmd_folder)

  # use this if you want to include modules from a subforder
  cmd_subfolder = os.path.realpath(os.path.abspath(os.path.join(os.path.split(inspect.getfile( inspect.currentframe() ))[0], folder)))
  if cmd_subfolder not in sys.path:
     sys.path.insert(0, cmd_subfolder)

if __name__ == '__main__':
  main()