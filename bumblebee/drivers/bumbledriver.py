import time

class bumbledriver():

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