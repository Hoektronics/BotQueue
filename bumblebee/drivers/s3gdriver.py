import bumbledriver

class s3gdriver(dumbledriver):
  def __init__(self, config):
    #super(S3GDriver, self).__init__(config)
    self.config = config
    self.connected = False