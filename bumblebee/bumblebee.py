import botqueueapi
import workerbee
import multiprocessing
import time
import hive
import logging
import curses
import webbrowser

class BumbleBee():
  def __init__(self):
    hive.loadLogger()
    self.log = logging.getLogger('botqueue')
    self.api = botqueueapi.BotQueueAPI()
    self.workers = []
    self.bots = []
    self.config = hive.config.get()

  def loadbot(self, pipe, data):
    try:
      self.log.info("Loading bot %s" % data['name'])
      worker = workerbee.WorkerBee(data, pipe)
      worker.run();
    except KeyboardInterrupt as e:
      self.log.debug("Bot %s exiting from keyboard interrupt." % data['name'])
    except Exception as ex:
      self.log.exception(ex)

  def main(self):
    #load up our bots and start processing them.
    self.log.info("Started up, loading bot list.")
    
    try:
      bots = self.api.listBots()
      if (bots['status'] == 'success'):
        for row in bots['data']:
          if (self.isOurBot(row)):
            #create our thread and start it.
            parent_conn, child_conn = multiprocessing.Pipe()
            p = multiprocessing.Process(target=self.loadbot, args=(child_conn,row,))
            p.start()
            
            #link = 'process' : p, 'pipe' : parent_conn }
            link = hive.Object()
            link.bot = row
            link.process = p
            link.pipe = parent_conn
            link.job = None
            self.workers.append(link)
          else:
            self.log.info("Skipping unknown bot %s" % row['name'])
      else:
        self.log.error("Bot list failure: %s" % bots['error'])

      curses.wrapper(self.mainMenu)
    except botqueueapi.NetworkError as e:
      self.log.error("Internet connection is down, quitting.")
    except KeyboardInterrupt as e:
      pass

  def mainMenu(self, screen):
    self.screen = screen
    self.screen.nodelay(1) #non-blocking, so we can refresh the screen

    lastUpdate = 0
    self.quit = False
    while not self.quit:
      if (time.time() - lastUpdate > 1):
        self.checkMessages()
        self.drawMenu()
      key = self.screen.getch()
      if key >= 0:
        if key == ord('.'): self.toggle()
        elif key == ord('q'):
          self.handleQuit()
      else:
        #sleep so we don't hog the CPU
        time.sleep(0.1)

  def handleQuit(self):
    self.quit = True
    self.log.info("Shutting down.")

    #tell all our threads to stop
    for link in self.workers:
      message = workerbee.Message('shutdown')
      link.pipe.send(message)

    #wait for all our threads to stop
    threads = len(self.workers)
    lastUpdate = 0
    while threads > 0:
      for idx, link in enumerate(self.workers):
        threads = 0
        if link.process.is_alive():
          threads = threads + 1
      if time.time() - lastUpdate > 1:
        self.screen.clear()
        self.screen.addstr("%s\n\n" % time.asctime())
        self.screen.addstr("Waiting for worker threads to shut down (%d/%d)" % (threads, len(self.workers)))
        self.screen.refresh()
        lastUpdate = time.time()

  #loop through our workers and check them all for messages
  def checkMessages(self):
    for link in self.workers:
      while link.pipe.poll():
        message = link.pipe.recv()
        self.handleMessage(link, message)

  #these are the messages we know about.
  def handleMessage(self, link, message):
    #self.log.debug("Got message %s" % message.name)
    if message.name == 'job_start':
      link.bot = message.data.bot
      link.job = message.data.job
    elif message.name == 'job_end':
      link.bot = message.data.bot
      link.job = message.data.job
      webbrowser.open_new("%s/job:%s/qa" % (self.config['app_url'], link.job['id']))
      curses.beep()
      curses.flash()
    elif message.name == 'slice_update':
      link.bot = message.data
      if link.bot['job']['slicejob']['status'] == 'pending':
        webbrowser.open_new("%s/slicejob:%s" % (self.config['app_url'], link.bot['job']['slicejob']['id']))
        curses.beep()
        curses.flash()
    elif message.name == 'print_error':
      pass
    elif message.name == 'human_required':
      pass
    elif message.name == 'job_update':
      link.job = message.data
    elif message.name == 'bot_update':
      link.bot = message.data
    
  def drawMenu(self):
    self.screen.clear()
    self.screen.addstr("%s\n\n" % time.asctime())
    self.screen.addstr("%6s  %20s  %10s  %8s  %8s  %10s\n" % ("ID", "BOT NAME", "STATUS", "PROGRESS", "JOB ID", "STATUS"))
    for link in self.workers:
      self.screen.addstr("%6s  %20s  %10s  " % (link.bot['id'], link.bot['name'], link.bot['status']))
      if (link.bot['status'] == 'working' or link.bot['status'] == 'waiting') and link.job:
        self.screen.addstr("  %0.2f%%  %8s  %10s" % (float(link.job['progress']), link.job['id'], link.job['status']))
      elif link.bot['status'] == 'error':
        self.screen.addstr("%s" % link.bot['error_text'])
      else:
        self.screen.addstr("   --         --         --")
      self.screen.addstr("\n")
    self.screen.addstr("\nq = quit program\n")
    self.screen.refresh()

  def isOurBot(self, bot):
    for row in self.config['workers']:
      if bot['name'] == row['name']:
        return True

    return False
if __name__ == '__main__':
  bee = BumbleBee()
  bee.main()