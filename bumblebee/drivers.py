import time
import printcore

class BumbleDriver():
  # empty for now
  def __init__(self, config):
    self.config = config
    self.lastUpdate = 0

  # execute one line
  def execute(self, line):
    pass

  #execute the whole damn thing.
  def executeGCodeFile(self, path):
    pass

  # this will really need to happen outside our thread, so we don't interrupt printing.
  def updatePercentage(self, latest):
    if (time.time() - self.lastUpdate > 30):
      self.log("%0.2f%%" % latest)
      self.lastUpdate = time.time()
      self.api.updateJobProgress(self.job['id'], "%0.5f" % latest)
    
class PrintcoreDriver(BumbleDriver):
  def __init__(self, config):
    #super(PrintcoreDriver, self).__init__(config)
    self.config = config
    self.connected = False

    self.p = printcore(config['port'],config['baud'])
    self.p.loud = True

  def executeGCodeFile(self, path):
    #TODO umm... does this open the entire file into memory?  that would be retardiculous.
    gcode=[i.replace("\n","") for i in open(path)]
    self.p.startprint(gcode)

    try:
      while(self.p.printing):
        time.sleep(1)
        self.updatePercentage(100*float(p.queueindex)/len(p.mainqueue))
    finally:
      self.p.disconnect()        

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

  def executeGCodeFile(self, path):
    for linenum, line in enumerate(path):
      self.execute(line)

      currentPosition = currentPosition + len(line)
      latest = float(currentPosition) / float(self.fileSize)*100
      self.updatePercentage(latest)

  def connect(self):
    self.connected = True

  def execute(self, line):
    time.sleep(0.001)
