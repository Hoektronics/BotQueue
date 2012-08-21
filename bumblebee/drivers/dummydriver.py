import bumbledriver
import time

class dummydriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    super(dummydriver, self).__init__(config)
    self.currentPosition = 0
    self.fileSize = 0

  def executeFile(self):
    self.currentPosition = 0
    self.fileSize 
    while 1:
      line = self.jobfile.readline()
      if not line:
          break
      time.sleep(0.001)
      self.currentPosition = self.currentPosition + len(line)

  def getPercentage(self):
    return float(self.currentPosition) / float(self.filesize)*100