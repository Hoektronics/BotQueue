import json
import pprint
import os
import shutil
import logging
import tempfile
import urllib2
import sys
import hashlib
import time
from threading import Thread
import subprocess
import drivers
import re

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
    self.cacheDir = getCacheDirectory()
    
  def load(self):
    self.prepareLocalFile()
    Thread(target=self.downloadFile).start()

  def getProgress(self):
    return self.progress

  #open our local file for writing.
  def prepareLocalFile(self):
    self.cacheHit = False
    try:
      self.localPath = self.cacheDir + self.remotefile['md5'] + "-" + os.path.basename(self.remotefile['name'])
      if os.path.exists(self.localPath):
        myhash = md5sumfile(self.localPath)
        if myhash != self.remotefile['md5']:
          os.unlink(self.localPath)
          self.log.warning("Existing file found: hashes did not match! %s != %s" % (myhash, self.remotefile['md5']))
        else:
          self.cacheHit = True
          self.localSize = os.path.getsize(self.localPath)
          self.localFile = open(self.localPath, "r")
          self.progress = 100
      #okay, should we open it for writing?
      if not os.path.exists(self.localPath):
        self.localFile = open(self.localPath, "w+")
    except Exception as ex:
      self.localFile = tempfile.NamedTemporaryFile()
      self.localPath = self.localFile.name

  #download our job and make sure its cool
  def downloadFile(self):
    #do we need to download it?
    if not self.cacheHit:
      while 1:
        try:
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
            os.unlink(self.localPath)
            raise Exception()
          else:
            self.progress = 100
            self.log.info("Download complete: %s" % self.remotefile['url'])
            self.localFile.seek(0)  
            return
        except Exception as ex:
          self.log.exception(ex)
          self.localFile.seek(0)  
          time.sleep(5)
    else:
      self.localFile.seek(0)  
      self.log.info("Using cached file %s" % self.localPath)

  def openUrl(self, url):
    request = urllib2.Request(url)
    #request.add_header('User-agent', 'Chrome XXX')
    urlfile = urllib2.urlopen(request)

    return urlfile

class Process():
  def __init__(self, command):
    self.info("Webcam Command: %s" % command)

    self.p = None
    self.outputLog = ""
    self.errorLog = ""

  def run(self):
    # this starts our thread to slice the model into gcode
    self.p = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
    while self.p.poll() is None:
      output = self.p.stdout.readline()
      if output:
        self.log.info("Process: %s" % output.strip())
        self.outputLog = self.outputLog + output
                    
      time.sleep(0.1)
    
    #get any last lines of output
    output = self.p.stdout.readline()
    while output:
      self.log.info("Process: %s" % output.strip())
      self.outputLog = self.outputLog + output
      output = self.p.stdout.readline()

    #get our errors (if any)
    error = self.p.stderr.readline()
    while error:
      self.log.error("Process: %s" % error.strip())
      self.errorLog = self.errorLog + error
      error = self.p.stderr.readline()

    self.info("Webcam: capture complete.")

    #did we get errors?
    if (self.errorLog or self.p.returncode > 0):
      self.log.error("Errors detected.  Bailing.")
      return False
    else:
      return True
  
  def kill(self):
    if self.p:
      try:
        self.log.info("Killing process %d." % self.p.pid)
        #self.p.terminate()
        os.kill(self.p.pid, signal.SIGTERM)
        t = 5 # max wait time in secs
        while self.p.poll() < 0:
          if t > 0.5:
            t -= 0.25
            time.sleep(0.25)
          else: # still there, force kill
            os.kill(self.p.pid, signal.SIGKILL)
            time.sleep(0.5)
        self.p.poll() # final try   
      except OSError as ex:
        #self.log.info("Kill exception: %s" % ex)
        pass #successfully killed process
 
class Object(object):
  pass

def md5sumfile(filename, block_size=2**18):
  md5 = hashlib.md5()
  f = open(filename, "r")
  while True:
    data = f.read(block_size)
    if not data:
      break
    md5.update(data)
  f.close()
  return md5.hexdigest()

def getCacheDirectory():
  global_config = config.get()
  if 'cache_directory' in global_config:
    cacheDir = global_config['cache_directory']
  else:
    realPath = os.path.dirname(os.path.realpath(__file__))
    cacheDir = "%s/cache/" % (realPath)
    
  #make it if it doesn't exist.
  if not os.path.isdir(cacheDir):
    os.mkdir(cacheDir)
    
  return cacheDir

def determineOS():
  if sys.platform.startswith('darwin'):
    return "osx"
  elif sys.platform.startswith('linux'):
    if os.uname()[4].startswith('arm'):
      return "raspberrypi"
    else:
      return "linux"
  else:
    return "unknown"

def scanBots():
  driver_names = ['printcoredriver']
  bots = {}
  for name in driver_names:
    module_name = 'drivers.' + name
    __import__(module_name)
    found = getattr(drivers, name).scanPorts()
    if found:
      bots[name] = found

  return bots

def scanCameras():
  cameras = []
  myos = determineOS()
  if myos == "osx":
    command = "./imagesnap -l"
  elif myos == "raspberrypi" or myos == "linux":
    command = "uvcdynctrl -l -v"

  returned = subprocess.check_output(command, shell=True)
  lines = returned.rstrip().split('\n')

  if myos == "osx":
    if len(lines) > 1:
      for line in lines[1:]:
        camera = {"id": line, "name": line, "device": line}
        cameras.append(camera)
  elif myos == "raspberrypi" or myos == "linux":
    for line in lines:
      matches = re.findall('(video\d+)[ ]+(.*) \[(.+), (.+)\]', line)
      if matches:
        camera = {"id": matches[0][3], "name": matches[0][1], "device": "/dev/" + matches[0][0]}
        cameras.append(camera)
      
  return cameras

def takePicture(device, watermark = None, output="webcam.jpg", brightness = 50, contrast = 50):
  log = logging.getLogger('botqueue')

  try:
    #what os are we using
    myos = determineOS()
    if myos == "osx":
      command = "./imagesnap -q -d '%s' -w 2.0 '%s' && sips --resampleWidth 640 --padToHeightWidth 480 640 --padColor FFFFFF -s formatOptions 60%% '%s' 2>/dev/null" % (
        device,
        output,
        output
      )
    elif myos == "raspberrypi" or myos == "linux":
      command = "exec /usr/bin/fswebcam -q --jpeg 60 -d '%s' -r 640x480 --title '%s' --set brightness=%d%% --set contrast=%d%% '%s'" % (
        device,
        watermark,
        output,
        brightness,
        contrast,
      )
    else:
      raise Exception("Webcams are not supported on your OS (%s)." % myos)

    log.info("Webcam Command: %s" % command)

    outputLog = ""
    errorLog = ""

    # this starts our thread to slice the model into gcode
    p = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
    log.info("Webcam Capture started.")
    while p.poll() is None:
      output = p.stdout.readline()
      if output:
        log.info("Webcam: %s" % output.strip())
        outputLog = outputLog + output

      time.sleep(0.5)

    #get any last lines of output
    output = p.stdout.readline()
    while output:
      log.debug("Webcam: %s" % output.strip())
      outputLog = outputLog + output
      output = p.stdout.readline()

    #get our errors (if any)
    error = p.stderr.readline()
    while error:
      log.error("Webcam: %s" % error.strip())
      errorLog = errorLog + error
      error = p.stderr.readline()

    log.info("Webcam: capture complete.")

    #did we get errors?
    if (errorLog or p.returncode > 0):
      log.error("Errors detected.  Bailing.")
      return False
    else:
      return True
  except Exception as ex:
    log.exception(ex)
    return False

def jsonNormalize(input):
  return json.loads(json.dumps(input))

def convertToString(input):
  if isinstance(input, dict):
    return {convertToString(key): convertToString(value) for key, value in input.iteritems()}
  elif isinstance(input, list):
    return [convertToString(element) for element in input]
  elif isinstance(input, unicode):
    return input.encode('utf-8')
  else:
    return input
          
def loadLogger():
  # create logger with 'spam_application'
  logger = logging.getLogger('botqueue')
  logger.setLevel(logging.DEBUG)

  # create formatter and add it to the handlers
  formatter = logging.Formatter('[%(asctime)s] %(levelname)s: %(message)s')

  # create file handler which logs even debug messages
  fh = logging.FileHandler('info.log')
  fh.setLevel(logging.DEBUG)
  fh.setFormatter(formatter)
  logger.addHandler(fh)

  # create console handler with a higher log level
  #ch = logging.StreamHandler()
  #ch.setLevel(logging.WARNING)
  #ch.setFormatter(formatter)
  #logger.addHandler(ch)

config = BeeConfig()
debug = pprint.PrettyPrinter(indent=4)
