import bumbledriver
import printcore
import time
from threading import Thread

#todo: this whole thing sucks.  we need a much better way to interface with this.
class printcoredriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    super(printcoredriver, self).__init__(config)
    
    self.p = printcore.printcore()
    self.p.loud = False
    time.sleep(2)

  def startPrint(self, jobfile, filesize):
    self.printing=True
    time.sleep(5) #wait for driver to initialize fully
    self.p.startprint(jobfile)
    Thread(target=self.printThreadEntry).start()

  #this doesn't do much, just a thread to watch our thread indirectly.
  def executeFile(self):
    while(self.p.printing):
      self.printing = self.p.printing
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
    
  def connect(self):
    if not self.isConnected():
      self.p.connect(self.config['port'], self.config['baud'])
      super(printcoredriver, self).connect()
    
  def disconnect(self):
    self.p.disconnect()
    super(printcoredriver, self).disconnect()