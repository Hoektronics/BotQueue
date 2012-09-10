import bumbledriver
import printcore
import time
from threading import Thread
import logging

#todo: this whole thing sucks.  we need a much better way to interface with this.
class printcoredriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    super(printcoredriver, self).__init__(config)
    
    self.p = printcore.printcore()
    self.p.loud = False
    time.sleep(2)
    self.log = logging.getLogger('botqueue')

  def startPrint(self, jobfile, filesize):
    try:
      self.printing=True
      self.connect()
      while not self.isConnected():
        time.sleep(1)
        self.log.debug("Waiting for driver to connect.")
      self.p.startprint(jobfile)
      Thread(target=self.printThreadEntry).start()
    except Exception as ex:
      self.log.error("Error starting print: %s" % ex)
      raise ex

  #this doesn't do much, just a thread to watch our thread indirectly.
  def executeFile(self):
    while(self.p.printing):
      self.printing = self.p.printing
      self.error = self.p.error
      self.errorMessage = self.p.errorMessage
      time.sleep(0.1)

  def getPercentage(self):
    return self.p.get_percentage()
    
  def pause(self):
    self.p.pause()
    super(printcoredriver, self).pause()
    
  def resume(self):
    self.p.resume()
    super(printcoredriver, self).resume()

  def reset(self):
    self.p.reset()
    super(printcoredriver, self).reset()

  def isConnected(self):
    return self.p.online
    
  def connect(self):
    if not self.isConnected():
      self.p.connect(self.config['port'], self.config['baud'])
      super(printcoredriver, self).connect()
    
  def disconnect(self):
    self.p.disconnect()
    super(printcoredriver, self).disconnect()