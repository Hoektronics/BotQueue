import json
import pprint
import os
import shutil

def log(message):
  print message
    
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

config = BeeConfig()
debug = pprint.PrettyPrinter(indent=4)