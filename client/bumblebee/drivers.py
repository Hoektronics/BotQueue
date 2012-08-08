import time

class BumbleDriver():
  # empty for now
  def __init__(self, config):
    self.config = config

class S3GDriver():
  def __init__(self, config):
    #super(S3GDriver, self).__init__(config)
    self.config = config
    
    
class SerialPassthruDriver():
  def __init__(self, config):
    #super(SerialPassthruDriver, self).__init__(config)
    self.config = config
    
class DummyDriver():
  def __init__(self, config):
    #super(DummyDriver, self).__init__(config)
    self.config = config

  def execute(self, line):
    time.sleep(0.001)