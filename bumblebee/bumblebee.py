import botqueueapi
import workerbee
import multiprocessing
import time
import hive
import logging
import curses

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
      worker = workerbee.WorkerBee(data)
      worker.run();
    except KeyboardInterrupt as e:
      self.log.debug("Bot %s exiting from keyboard interrupt." % data['name'])

  def main(self):
    #load up our bots and start processing them.
    self.log.info("Started up, loading bot list.")
    
    try:
      bots = self.api.listBots()
      if (bots['status'] == 'success'):
        for row in bots['data']:
          if (self.isOurBot(row)):
            parent_conn, child_conn = multiprocessing.Pipe()
            p = multiprocessing.Process(target=self.loadbot, args=(child_conn,row,))
            p.start()
            link = { 'process' : p, 'pipe' : parent_conn }
            self.workers.append(link)
            self.bots.append(row)
            time.sleep(0.5) # give us enough time to avoid contention when getting jobs.
          else:
            self.log.info("Skipping unknown bot %s" % row['name'])
      else:
        self.log.error("Bot list failure: %s" % bots['error'])

      curses.wrapper(self.mainMenu)

      #webbrowser.open_new(self.getAuthorizeUrl())

      for link in self.workers:
        link['process'].join()
    except KeyboardInterrupt as e:
      pass

  def mainMenu(self, screen):
    self.screen = screen
    self.screen.nodelay(1) #non-blocking, so we can refresh the screen

    lastUpdate = 0
    quit = False
    while not quit:
      if (time.time() - lastUpdate > 1):
        self.drawMenu()
      key = self.screen.getch()
      if key >= 0:
        if key == ord('.'): self.toggle()
        elif key == ord('q'):
          quit = True

  def drawMenu(self):
    self.screen.clear()
    self.screen.addstr("q = quit program\n")
    for idx, bot in enumerate(self.bots):
      self.screen.addstr("%s = %s\n" % (idx, bot['name']))
    

  def isOurBot(self, bot):
    for row in self.config['workers']:
      if bot['name'] == row['name']:
        return True

    return False
if __name__ == '__main__':
  bee = BumbleBee()
  bee.main()