import json
import pprint
import os
import shutil
import logging

class BeeConfig():
  
  def __init__(self):
    self.data = []
    self.loaded = False
          
  def get(self):
    if not self.loaded:
      self.load()
    return self.data

  def load(self):
    try:
      if not os.path.exists("config.json"):
        shutil.copy("config-dist.json", "config.json")
      f = open("config.json", "r")
      self.data = json.load(f)
      f.close()
    
      return f
    except ValueError as e:
      print("Error parsing config file: %s" % e)
      raise RuntimeError("Error parsing config file: %s" % e)     
    
  def save(self, data):
    f = open("config.json", "w")
    f.write(json.dumps(data, indent=2))
    f.close()    
    self.data = data

class Object(object):
  pass
  
def loadLogger():
  # create logger with 'spam_application'
  logger = logging.getLogger('botqueue')
  logger.setLevel(logging.DEBUG)
  # create file handler which logs even debug messages
  fh = logging.FileHandler('info.log')
  fh.setLevel(logging.DEBUG)
  # create console handler with a higher log level
  ch = logging.StreamHandler()
  ch.setLevel(logging.WARNING)
  # create formatter and add it to the handlers
  formatter = logging.Formatter('[%(asctime)s] %(levelname)s: %(message)s')
  fh.setFormatter(formatter)
  ch.setFormatter(formatter)
  # add the handlers to the logger
  logger.addHandler(fh)
  logger.addHandler(ch)

  # logger.debug('Quick zephyrs blow, vexing daft Jim.')
  # logger.info('How quickly daft jumping zebras vex.')
  # logger.warning('Jail zesty vixen who grabbed pay from quack.')
  # logger.error('The five boxing wizards jump quickly.')

config = BeeConfig()
debug = pprint.PrettyPrinter(indent=4)