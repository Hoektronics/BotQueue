import botqueueapi
import workerbee
import threading
import Queue
import time
import hive
import logging
import curses
import webbrowser
import hashlib
import stacktracer

class BumbleBee():
  
  sleepTime = 0.5
  
  def __init__(self):
    hive.loadLogger()
    self.log = logging.getLogger('botqueue')
    self.api = botqueueapi.BotQueueAPI()
    self.workers = []
    self.bots = []
    self.workerDataAge = {}
    self.config = hive.config.get()
    
    #check for default info.
    if not 'can_slice' in self.config:
      self.config['can_slice'] = True
      hive.config.save(self.config)

    #check for default info.
    if not 'app_url' in self.config:
      self.config['app_url'] = "https://www.botqueue.com"
      hive.config.save(self.config)

    #create a unique hash that will identify this computers requests
    if not self.config['uid']:
      self.config['uid'] = hashlib.sha1(str(time.time())).hexdigest()
      hive.config.save(self.config)   
      
    #this is our threading tracker
    stacktracer.trace_start("trace.html", interval=5, auto=True) # Set auto flag to always update file!

  def loadBot(self, mosi_queue, miso_queue, data):
    try:
      self.log.info("Loading bot %s" % data['name'])
      worker = workerbee.WorkerBee(data, mosi_queue, miso_queue)
      worker.run();
    except KeyboardInterrupt as e:
      self.log.debug("Bot %s exiting from keyboard interrupt." % data['name'])
    except Exception as ex:
      self.log.exception(ex)

  def getBots(self):

    startTime = time.time()
    bots = self.api.listBots()
    self.checkMessages() #must come after listbots

    if bots:
      if (bots['status'] == 'success'):
        for row in bots['data']:
          if self.isOurBot(row):
            link = self.getWorker(row['id'])
            if link:
              if not (row['id'] in self.workerDataAge):
                self.workerDataAge[row['id']] = 0
              if self.workerDataAge[row['id']] < startTime:
                self.sendMessage(link, 'updatedata', row)
                link.bot = row
                self.workerDataAge[row['id']] = startTime
              else:
                self.log.debug("Worker for %s is stale: %s / %s" % (row['name'], startTime, self.workerDataAge[row['id']]))
            else:
              self.log.info("Creating worker thread for bot %s" % row['name'])
              #create our thread and start it.
              #master_in, slave_out = multiprocessing.Pipe()
              #slave_in, master_out = multiprocessing.Pipe()
              mosi_queue = Queue.Queue()
              miso_queue = Queue.Queue()
              p = threading.Thread(target=self.loadBot, args=(mosi_queue, miso_queue, row,))
              p.name = "Bot-%s" % row['name']
              p.daemon = True
              p.start()

              #make our link object to track all this cool stuff.
              link = hive.Object()
              link.bot = row
              link.process = p
              link.miso_queue = miso_queue
              link.mosi_queue = mosi_queue
              link.job = None
              self.workers.append(link)
            
            #should we find a new job?
            if link.bot['status'] == 'idle':
              self.log.debug("Getting new job for bot")
              self.getNewJob(link)
          # else:
          #   self.log.info("Skipping unknown bot %s" % row['name'])
      else:
        self.log.error("Bot list failure: %s" % bots['error'])

  def getWorker(self, id):
    for link in self.workers:
      if link.bot['id'] == id:
        return link
    return False

  def main(self):
    #load up our bots and start processing them.
    self.log.info("Started up, loading bot list.")

    curses.wrapper(self.mainMenu)
      
  def mainMenu(self, screen):
    try:
      self.screen = screen
      self.screen.nodelay(1) #non-blocking, so we can refresh the screen

      #Try/except for the terminals that don't support hiding the cursor
      try: 
          curses.curs_set(0)
      except:
          pass

      #when did we last update?
      lastBotUpdate = 0
      lastScreenUpdate = 0

      #show an intro screen.
      # self.screen.erase()
      # self.screen.addstr("\nBotQueue v%s starting up - loading bot list.\n\n" % self.version)
      # self.screen.refresh()
    
      #our main loop until we're done.
      self.quit = False
      while not self.quit:

        #any messages?
        self.checkMessages()
        if (time.time() - lastScreenUpdate > 1):
          self.drawMenu()
          lastScreenUpdate = time.time()
        if (time.time() - lastBotUpdate > 10):
          self.getBots()
          lastBotUpdate = time.time()

        #keyboard interface stuff.
        key = self.screen.getch()
        if key >= 0:
          if key == ord('.'):
            self.toggle()
          elif key == ord('q'):
            self.handleQuit()

        time.sleep(self.sleepTime)
    except KeyboardInterrupt:
      self.handleQuit()
    
  def handleQuit(self):
    self.quit = True
    self.log.info("Shutting down.")
    
    #tell all our threads to stop
    for link in self.workers:
      self.sendMessage(link, 'shutdown')

    #wait for all our threads to stop
    threads = len(self.workers)
    lastUpdate = 0
    while threads > 0:
      for idx, link in enumerate(self.workers):
        threads = 0
        if link.process.is_alive():
          threads = threads + 1
      if time.time() - lastUpdate > 1:
        self.screen.erase()
        self.screen.addstr("%s\n\n" % time.asctime())
        self.screen.addstr("Waiting for worker threads to shut down (%d/%d)" % (threads, len(self.workers)))
        self.screen.refresh()
        lastUpdate = time.time()
        
    #stop our thread tracking.
    stacktracer.trace_stop()
        
    self.screen.erase()    

  def sendMessage(self, link, name, data = False):
    self.checkMessages()
    #self.log.debug("Mothership: sending message")
    message = workerbee.Message(name, data)
    link.mosi_queue.put(message)

  #loop through our workers and check them all for messages
  def checkMessages(self):
    #self.log.debug("Mothership: Checking messages.")
    for link in self.workers:
      while not link.miso_queue.empty():
        message = link.miso_queue.get(False)
        self.handleMessage(link, message)
        link.miso_queue.task_done()

  #these are the messages we know about.
  def handleMessage(self, link, message):
    #self.log.debug("Mothership got message %s" % message.name)
    if message.name == 'job_update':
      link.job = message.data
    elif message.name == 'bot_update':
      if link.bot['status'] != message.data['status']:
        self.log.info("Mothership: %s status changed from %s to %s" % (link.bot['name'], link.bot['status'], message.data['status']))
      link.bot = message.data
      self.workerDataAge[message.data['id']] = time.time()
    
  def drawMenu(self):
    #self.log.debug("drawing screen")
    
    try:
      self.screen.erase()
      self.screen.addstr("BotQueue v%s Time: %s\n\n" % (self.api.version, time.asctime()))
      self.screen.addstr("%6s  %20s  %10s  %8s  %8s  %10s\n" % ("ID", "BOT NAME", "STATUS", "PROGRESS", "JOB ID", "STATUS"))
      for link in self.workers:
        self.screen.addstr("%6s  %20s  %10s  " % (link.bot['id'], link.bot['name'], link.bot['status']))
        if (link.bot['status'] == 'working' or link.bot['status'] == 'waiting' or link.bot['status'] == 'slicing') and link.job:
          self.screen.addstr("  %0.2f%%  %8s  %10s" % (float(link.job['progress']), link.job['id'], link.job['status']))
        elif link.bot['status'] == 'error':
          self.screen.addstr("%s" % link.bot['error_text'])
        else:
          self.screen.addstr("   --         --         --")
        self.screen.addstr("\n")
      self.screen.addstr("\nq = quit program\n")

      #show our network status.
      self.screen.addstr("\nNetwork Status: ")
      if self.api.netStatus == True:
        self.screen.addstr("ONLINE")
      else:
        self.screen.addstr("OFFLINE")

      self.screen.refresh()
    except curses.error as ex:
      self.log.error("Problem drawing screen - to small? %s" % ex)

  def isOurBot(self, bot):
    for row in self.config['workers']:
      if bot['name'] == row['name']:
        return True
    return False

  def getNewJob(self, link):
    self.log.info("Looking for new job.")

    result = self.api.findNewJob(link.bot['id'], self.config['can_slice'])
    if (result['status'] == 'success'):
      if (len(result['data'])):
        job = result['data']
        startTime = time.time()
        jresult = self.api.grabJob(link.bot['id'], job['id'], self.config['can_slice'])

        if (jresult['status'] == 'success'):
          #save it to our link.
          link.job = job
          link.bot['job'] = job
          
          #notify the bot
          self.sendMessage(link, 'updatedata', link.bot)
          self.workerDataAge[link.bot['id']] = startTime

          return True
        #else:
        #  raise Exception("Error grabbing job: %s" % jresult['error'])
      # else:
      #   self.getOurInfo() #see if our status has changed.
    else:
      raise Exception("Error finding new job: %s" % result['error'])
    return False

if __name__ == '__main__':
  bee = BumbleBee()
  bee.main()
