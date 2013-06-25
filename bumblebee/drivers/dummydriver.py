import bumbledriver
import time
import logging

class dummydriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    super(dummydriver, self).__init__(config)
    self.currentPosition = 0
    self.connected = True

  def startPrint(self, jobfile):
    try:
      self.connected = True
      self.printing = False
      super(dummydriver, self).startPrint(jobfile)
    except Exception as ex:
      self.log.exception(ex)

  def executeFile(self):
    try:
      delay = float(self.config.get('delay', 0.05))
      self.config['delay'] = delay

      self.currentPosition = 0
      self.jobfile.localFile.seek(0)
      self.log.debug("Dummy Driver starting file w/ delay of %s" % self.config['delay'])
      while self.printing:
        line = self.jobfile.localFile.readline()
        if not line:
            break
        time.sleep(delay)
        self.currentPosition = self.currentPosition + len(line)
    except Exception as ex:
      self.log.exception(ex)

  def getPercentage(self):
    return float(self.currentPosition) / float(self.jobfile.localSize)*100
