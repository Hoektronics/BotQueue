import json
import pprint
import os
import shutil
import logging
import tempfile
import urllib2
import sys
import hashlib
from threading import Thread

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

class URLFile():
  
  def __init__(self, filedata):
    self.global_config = config.get()
    self.log = logging.getLogger('botqueue')

    #init our local variables.
    self.remotefile = filedata
    self.cacheHit = False
    self.localPath = False
    self.localFile = False
    self.localSize = 0
    self.progress = 0
    self.cacheDir = False
    self.checkCacheDirectory()
    
  def load(self):
    self.prepareLocalFile()
    Thread(target=self.downloadFile).start()

  def getProgress(self):
    return self.progress

  #see if we have some sort of caching dir setup.
  def checkCacheDirectory(self):
    if 'cache_directory' in self.global_config:
      self.cacheDir = self.global_config['cache_directory']
    else:
      realPath = os.path.dirname(os.path.realpath(__file__))
      self.cacheDir = "%s/cache/" % (realPath)
      
    #make it if it doesn't exist.
    if not os.path.isdir(self.cacheDir):
      os.mkdir(self.cacheDir)

  #open our local file for writing.
  def prepareLocalFile(self):
    self.cacheHit = False
    try:
      self.localPath = self.cacheDir + self.remotefile['md5'] + "-" + os.path.basename(self.remotefile['name'])
      if os.path.exists(self.localPath):
        myhash = self.md5sumfile(self.localPath)
        if myhash != self.remotefile['md5']:
          self.log.warning("Existing file found: hashes did not match! %s != %s" % (myhash, self.remotefile['md5']))
          raise Exception
        else:
          self.cacheHit = True
          self.localSize = os.path.getsize(self.localPath)
          self.localFile = open(self.localPath, "r")
          self.progress = 100
      else:
        self.localFile = open(self.localPath, "w+")
    except Exception as ex:
      self.localFile = tempfile.NamedTemporaryFile()
      self.localPath = self.localFile.name

  #download our job and make sure its cool
  def downloadFile(self):
    #do we need to download it?
    if not self.cacheHit:
      #download our file.
      self.log.info("Downloading %s to %s" % (self.remotefile['url'], self.localPath))
      urlFile = self.openUrl(self.remotefile['url'])
      chunk = 4096
      md5 = hashlib.md5()
      self.localSize = 0
      while 1:
        data = urlFile.read(chunk)
        if not data:
          break
        md5.update(data)
        self.localFile.write(data)
        self.localSize = self.localSize + len(data)
        self.progress = float(self.localSize) / float(self.remotefile['size'])*100

      #check our final md5 sum.
      if md5.hexdigest() != self.remotefile['md5']:
        self.log.error("Downloaded file hash did not match! %s != %s" % (md5.hexdigest(), self.remotefile['md5']))
        raise Exception()
      else:
        self.progress = 100
        self.log.info("Download complete: %s" % self.remotefile['url'])
    else:
      self.log.info("Using cached file %s" % self.localPath)

    #reset to the beginning.
    self.localFile.seek(0)  

  def openUrl(self, url):
    request = urllib2.Request(url)
    #request.add_header('User-agent', 'Chrome XXX')
    urlfile = urllib2.urlopen(request)

    return urlfile

  def md5sumfile(self, filename, block_size=2**18):
    md5 = hashlib.md5()
    f = open(filename, "r")
    while True:
      data = f.read(block_size)
      if not data:
        break
      md5.update(data)
    f.close()
    return md5.hexdigest()
  
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