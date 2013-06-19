import time
import logging
from threading import Thread
import sys
import tempfile
import subprocess
import os
import signal
import hive
import re

class Ginsu():
  
  def __init__(self, sliceFile, sliceJob):
    self.log = logging.getLogger('botqueue')
    self.sliceFile = sliceFile
    self.sliceJob = sliceJob
    self.slicer = False
    self.sliceThread = False
    self.sliceResult = False

  def isRunning(self):
    return self.slicer.isRunning()

  def stop(self):
    self.log.debug("Ginsu - stopping slice job.")
    if self.slicer:
      self.slicer.stop()
    if self.sliceThread:
      self.sliceThread.join(10)

  def getProgress(self):
    if self.slicer:
      return self.slicer.getProgress()
    else:
      return 0

  def getResult(self):
    return self.sliceResult

  def slicerFactory(self):
    path = self.sliceJob['slice_config']['engine']['path']
    mySlic3r = Slic3r(self.sliceJob['slice_config'], self.sliceFile)
    # getSlicerPath is called to verify the engine exists and
    # is available for this OS
    mySlic3r.getSlicerPath()
    return mySlic3r

  def slice(self):
    self.log.info("Starting slice.")
    self.slicer = self.slicerFactory()

    self.sliceThread = Thread(target=self.threadEntry).start()
    
  def threadEntry(self):
    self.sliceResult = self.slicer.slice()
    
class GenericSlicer(object):
  def __init__(self, config, slicefile):
    self.config = config
    self.log = logging.getLogger('botqueue')
    
    self.sliceFile = slicefile
    self.progress = 0
    self.running = True
    
    self.prepareFiles()

  def stop(self):
    self.log.debug("Generic slicer stopped.")
    self.running = False
    
  def isRunning(self):
    return self.running
    
  def prepareFiles(self):
    pass
    
  def slice(self):
    pass
    
  def getProgress(self):
    return self.progress
      
class Slic3r(GenericSlicer):
  def __init__(self, config, slicefile):
    super(Slic3r, self).__init__(config, slicefile)
 
    self.p = False  
 
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
  
  def stop(self):
    self.log.debug("Slic3r slicer stopped.")
    if self.p:
      try:
        self.log.info("Killing slic3r process.")
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
      self.log.info("Slicer killed.")
    self.running = False
 
  def prepareFiles(self):
    self.configFile = tempfile.NamedTemporaryFile(delete=False)
    self.configFile.write(self.config['config_data'])
    self.configFile.flush()
    self.log.debug("Config file: %s" % self.configFile.name)
    
    self.outFile = tempfile.NamedTemporaryFile(delete=False)
    self.log.debug("Output file: %s" % self.outFile.name)

  def getSlicerPath(self):
    #figure out where our path is.
    myos = hive.determineOS()
    if myos == "osx":
      osPath = "osx.app/Contents/MacOS/slic3r"
    elif myos == "raspberrypi":
      osPath = "raspberrypi/slic3r"
    elif myos == "linux":
        osPath = "linux/bin/slic3r"
    else:
      raise Exception("Slicing is not supported on your OS.")

    realPath = os.path.dirname(os.path.realpath(__file__))
    sliceEnginePath = "%s/slicers/%s" % (realPath, self.config['engine']['path'])
    slicePath = "%s/%s" % (sliceEnginePath osPath)
    self.log.debug("Slicer path: %s" % slicePath)
    if os.path.exists(slicePath) == False:
	if os.path.exists(sliceEnginePath):
            raise Exception("This engine isn't supported on your OS.")
        else:
            raise Exception("The requested engine isn't installed.")
    
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
      command = "exec %s --load %s --output %s %s" % (
        self.getSlicerPath(),
        self.configFile.name,
        self.outFile.name,
        self.sliceFile.localPath
      )
      self.log.info("Slice Command: %s" % command)

      outputLog = ""
      errorLog = ""
      
      # this starts our thread to slice the model into gcode
      self.p = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
      self.log.info("Slic3r started.")
      while self.p.poll() is None:
        output = self.p.stdout.readline()
        if output:
          self.log.info("Slic3r: %s" % output.strip())
          outputLog = outputLog + output
          self.checkProgress(output)
                        
        time.sleep(0.1)
        
        #did we get cancelled?
        if not self.running:
          self.log.info("Killing slic3r process.")
          self.p.terminate()
          self.p.kill()
          return

        # this code does not work for some reason and ends up blocking the loop until program exits if there is no errors
        # this is a bummer, because we can't get realtime error logging.  :(
        # err = self.p.stderr.readline().strip()
        #         if err:
        #           self.log.error("Slic3r: %s" % error)
        #           errorLog = errorLog + err         

      #get any last lines of output
      output = self.p.stdout.readline()
      while output:
        self.log.debug("Slic3r: %s" % output.strip())
        outputLog = outputLog + output
        self.checkProgress(output)
        output = self.p.stdout.readline()

      #get our errors (if any)
      error = self.p.stderr.readline()
      while error:
        self.log.error("Slic3r: %s" % error.strip())
        errorLog = errorLog + error
        error = self.p.stderr.readline()

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
      elif self.p.returncode > 0:
        sushi.status = "failure"
        self.log.error("Program returned code %s" % self.p.returncode)
      else:
        sushi.status = "complete"
    
      #okay, we're done!
      self.running = False
      
      return sushi
    except Exception as ex:
      self.log.exception(ex)
