import time
import logging
from threading import Thread
import sys
import tempfile
import subprocess
import os

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
  
  def prepareFiles(self):
    self.configFile = tempfile.NamedTemporaryFile(delete=False)
    self.configFile.write(self.config['config_data'])
    self.configFile.flush()
    self.log.debug("Config file: %s" % self.configFile.name)
    
    self.startFile = tempfile.NamedTemporaryFile(delete=False)
    self.startFile.write(self.config['start_gcode'])
    self.startFile.flush()
    self.log.debug("Start GCode file: %s" % self.startFile.name)

    self.endFile = tempfile.NamedTemporaryFile(delete=False)
    self.endFile.write(self.config['end_gcode'])
    self.endFile.flush()
    self.log.debug("End Gcode file: %s" % self.endFile.name)
    
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

  def slice(self):
    #create our command to do the slicing
    try:
      command = "%s --load %s --output %s --start-gcode %s --end-gcode %s %s" % (
        self.getSlicerPath(),
        self.configFile.name,
        self.outFile.name,
        self.startFile.name,
        self.endFile.name,
        self.sliceFile.localPath
      )
      self.log.debug("Slice Command: %s" % command)

      outputFile = tempfile.NamedTemporaryFile()
      errorFile = tempfile.NamedTemporaryFile()
      result = subprocess.call(command, stdout=outputFile, stderr=errorFile, shell=True)
      outputFile.flush()
      outputFile.seek(0)
      errorFile.flush()
      errorFile.seek(0)
      self.log.debug("Output: %s" % outputFile.read())
      self.log.debug("Errors: %s" % errorFile.read())
      self.log.debug("Result: %s" % result)
    except Exception as ex:
      self.log.exception(ex)
    
    #actually run our command
    
    #parse the results to get progress, and filament required
    
    #save all our results to an object
    result = hive.Object
    result.status = "success"
    result.output_file = self.outFile.name
    #result.output_log = output_log
    
    return result