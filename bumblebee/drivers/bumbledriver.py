import time
from threading import Thread

class bumbledriver(object):

  def __init__(self, config):
    self.config = config
    self.printing = False
    self.paused = False
    self.connected = False
    self.jobfile = None
    self.filesize = 0
    self.error = False
    self.errorMessage = None

  def executeFile(self):
    pass

  def connect(self):
    self.connected = True
    
  def disconnect(self):
    self.connected = False
    
  def pause(self):
    self.paused = True
    
  def resume(self):
    self.paused = False
  
  def stop(self):
    self.printing = False
    self.reset()

  def reset(self):
    pass

  def startPrint(self, jobfile):
    if(self.isRunning() or not self.isConnected()):
        return False

    self.jobfile = jobfile
    self.printing=True
    Thread(target=self.printThreadEntry).start()

  def printThreadEntry(self):
    self.executeFile()
    self.finishPrint()

  def finishPrint(self):
    self.printing = False
    
  def getPercentage(self):
    return 0

  def getStatus(self):
    pass
    
  def hasError(self):
    return self.error

  def getErrorMessage(self):
    return self.errorMessage
    
  def isConnected(self):
    return self.connected
    
  def isRunning(self):
    return self.printing
    
  def isPaused(self):
    return self.paused

  # is this chaff?
  # this will really need to happen outside our thread, so we don't interrupt printing.
  def phoneHome(self, latest):
    if (time.time() - self.lastUpdate > 30):
      print "%0.2f%%" % latest
      self.lastUpdate = time.time()
      self.api.updateJobProgress(self.job['id'], "%0.5f" % latest)