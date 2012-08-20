import bumbledriver
import time

class dummydriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    #super(DummyDriver, self).__init__(config)
    self.config = config
    self.connected = False

  def connect(self):
    self.connected = True

  def execute(self, line):
    time.sleep(0.001)