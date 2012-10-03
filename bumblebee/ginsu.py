import time
import logging
from threading import Thread
import sys
import tempfile
import subprocess
import os
import hive
import string
import re

class Ginsu():
  
  def __init__(self, sliceFile, sliceJob):
    self.log = logging.getLogger('botqueue')
    self.sliceFile = sliceFile
    self.sliceJob = sliceJob

    self.running = False

  def isRunning(self):
    return self.running
    
  def getProgress(self):
    return self.slicer.getProgress()

  def getResult():
    return self.sliceResult

  def slicerFactory(self):
    path = self.sliceJob['slice_config']['engine']['path']
    if (path == 'slic3r-0.9.2'):
      return Slic3r(self.sliceJob['slice_config'], self.sliceFile)
    else:
      raise Exception("Unknown slicer path specified: %s" % path)    

  def slice(self):
    self.log.debug("Starting slice.")
    self.running = True
    self.slicer = self.slicerFactory()

    Thread(target=self.threadEntry).start()
    
  def threadEntry(self):
    self.sliceResult = self.slicer.slice()
    self.running = False
    
class GenericSlicer(object):
  def __init__(self, config, slicefile):
    self.config = config
    self.log = logging.getLogger('botqueue')
    
    self.sliceFile = slicefile
    self.progress = 0
    
    self.prepareFiles()

  def prepareFiles(self):
    pass
    
  def slice(self):
    pass
    
  def getProgress(self):
    return self.progress
      
class Slic3r(GenericSlicer):
  def __init__(self, config, slicefile):
    super(Slic3r, self).__init__(config, slicefile)
    
    #our regexes
    self.reg05 = re.compile('Processing input file')
    self.reg10 = re.compile('Processing triangulated mesh')
    self.reg20 = re.compile('Generating perimeters')
    self.reg30 = re.compile('Detecting solid surfaces')
    self.reg40 = re.compile('Preparing infill surfaces')
    self.reg50 = re.compile('Detect bridges')
    self.reg60 = re.compile('Generating horizontal shells')
    self.reg70 = re.compile('Combining infill')
    self.reg80 = re.compile('Infilling layers')
    self.reg90 = re.compile('Generating skirt')
    self.reg100 = re.compile('Exporting G-code to')
  
  def prepareFiles(self):
    self.configFile = tempfile.NamedTemporaryFile(delete=False)
    self.configFile.write(self.config['config_data'])
    self.configFile.flush()
    self.log.debug("Config file: %s" % self.configFile.name)
    
    self.outFile = tempfile.NamedTemporaryFile(delete=False)
    self.log.debug("Output file: %s" % self.outFile.name)

  def getSlicerPath(self):
    #figure out where our path is.
    if sys.platform.startswith('darwin'):
      osPath = "osx.app/Contents/MacOS/slic3r"
    elif sys.platform.startswith('linux'):
      osPath = "linux/bin/slic3r"
    else:
      raise Exception("Slicing is not supported on your OS.")

    realPath = os.path.dirname(os.path.realpath(__file__))
    slicePath = "%s/slicers/%s/%s" % (realPath, self.config['engine']['path'], osPath)
    self.log.debug("Slicer path: %s" % slicePath)
    
    return slicePath

  def checkProgress(self, line):
    if self.reg05.search(line):
      self.progress = 5
    elif self.reg10.search(line):
      self.progress = 10
    elif self.reg20.search(line):
      self.progress = 20
    elif self.reg30.search(line):
      self.progress = 30
    elif self.reg40.search(line):
      self.progress = 40
    elif self.reg50.search(line):
      self.progress = 50
    elif self.reg60.search(line):
      self.progress = 60
    elif self.reg70.search(line):
      self.progress = 70
    elif self.reg80.search(line):
      self.progress = 80
    elif self.reg90.search(line):
      self.progress = 90
    elif self.reg100.search(line):
      self.progress = 100
            
  def slice(self):
    #create our command to do the slicing
    try:
      command = "%s --load %s --output %s %s" % (
        self.getSlicerPath(),
        self.configFile.name,
        self.outFile.name,
        self.sliceFile.localPath
      )
      self.log.debug("Slice Command: %s" % command)

      outputLog = ""
      errorLog = ""
      
      # this starts our thread to slice the model into gcode
      p = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
      self.log.debug("Slic3r started.")
      while p.poll() is None:
        output = p.stdout.readline()
        if output:
          self.log.debug("Slic3r: %s" % output.strip())
          outputLog = outputLog + output
          self.checkProgress(output)
                        
        time.sleep(1)

        # this code does not work for some reason and ends up blocking the loop until program exits if there is no errors
        # this is a bummer, because we can't get realtime error logging.  :(
        # err = p.stderr.readline().strip()
        #         if err:
        #           self.log.error("Slic3r: %s" % error)
        #           errorLog = errorLog + err         

      #get any last lines of output
      output = p.stdout.readline()
      while output:
        self.log.debug("Slic3r: %s" % output.strip())
        outputLog = outputLog + output
        self.checkProgress(output)
        output = p.stdout.readline()

      #get our errors (if any)
      error = p.stderr.readline()
      while error:
        self.log.error("Slic3r: %s" % error.strip())
        errorLog = errorLog + error
        error = p.stderr.readline()

      #give us 1 second for the main loop to pull in our finished status.
      time.sleep(1)

      #save all our results to an object
      sushi = hive.Object
      sushi.output_file = self.outFile.name
      sushi.output_log = outputLog
      sushi.error_log = errorLog

      #did we get errors?
      if errorLog:
        sushi.status = "pending"
      #unknown return code... failure
      elif p.returncode > 0:
        sushi.status = "failure"
        self.log.error("Program returned code %s" % p.returncode)
      else:
        sushi.status = "complete"
      
      return sushi
    except Exception as ex:
      self.log.exception(ex)