import time
import printcore
import os

class BumbleDriver():

  lastUpdate = 0
  
  def __init__(self, config):
    self.config = config
    self.lastUpdate = 0

  # execute one line
  # def execute(self, line):
  #   pass

  def connect(self):
    pass
    
  def disconnect(self):
    pass
    
  def pause(self):
    pass
    
  def resume(self):
    pass

  def startPrint(self, file):
    pass

  def printThreadEntry(self):
    pass

  def sendGCode(self, line):
    pass
    
  def sendGCodeNow(self, line):
    pass
    
  def getPercentage(self):
    pass

  def getStatus(self):
    pass

  def isConnected():
    pass
    
  def isRunning():
    pass

  # this will really need to happen outside our thread, so we don't interrupt printing.
  def phoneHome(self, latest):
    if (time.time() - self.lastUpdate > 30):
      print "%0.2f%%" % latest
      self.lastUpdate = time.time()
      self.api.updateJobProgress(self.job['id'], "%0.5f" % latest)
    
#todo: this whole thing sucks.  we need a much better way to interface with this.
class PrintcoreDriver(BumbleDriver):
  def __init__(self, config):
    #super(PrintcoreDriver, self).__init__(config)
    self.config = config
    self.connected = False
    
    self.p = printcore(config['port'],config['baud'])
    self.p.loud = True

  def execute(self, line):
    pass
  # def executeGCodeFile(self, path):
  #   #TODO umm... does this open the entire file into memory?  that would be retardiculous.
  #   gcode=[i.replace("\n","") for i in open(path)]
  #   self.p.startprint(gcode)
  # 
  #   try:
  #     while(self.p.printing):
  #       time.sleep(1)
  #       self.updatePercentage(100*float(p.queueindex)/len(p.mainqueue))
  #   finally:
  #     self.p.disconnect()        

class S3GDriver(BumbleDriver):
  def __init__(self, config):
    #super(S3GDriver, self).__init__(config)
    self.config = config
    self.connected = False
    
class DummyDriver(BumbleDriver):
  def __init__(self, config):
    #super(DummyDriver, self).__init__(config)
    self.config = config
    self.connected = False

  def connect(self):
    self.connected = True

  def execute(self, line):
    time.sleep(0.001)
