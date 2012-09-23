import time
import logging
from threading import Thread
import sys

class Ginsu():
  
  def __init__(self, sliceFile, sliceJob):
    self.log = logging.getLogger('botqueue')
    self.sliceFile = sliceFile
    self.sliceJob = sliceJob

    self.isRunning = False

  def isRunning(self):
    return self.isRunning
    
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
    Thread(target=self.printThreadEntry).start()
    
  def threadEntry(self):
    self.isRunning = True

    self.slicer = self.slicerFactory()
    self.sliceResult = self.slicer.slice()
    
    self.isRunning = False
    
class GenericSlicer(object):
  def __init__(self, config, sliceFile):
    self.config = config
    self.sliceFile = sliceFile
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
    super(GenericSlicer, self).__init__(config)
  
  def prepareFiles(self):
    self.startFile = tempfile.NamedTemporaryFile(delete=False)
    self.startFile.write(self.config['slice_config']['start_gcode'])
    
    self.endFile = tempfile.NamedTemporaryFile(delete=False)
    self.endFile.write(self.config['slice_config']['end_gcode'])

    self.configFile = tempfile.NamedTemporaryFile(delete=False)
    self.configFile.write(self.config['slice_config']['config_data'])
    
    self.outFile = tempfile.NamedTemporaryFile(delete=False)

  def getSlicerPath(self):
    #figure out where our path is.
    if sys.platform.startswith('darwin'):
      osPath = "osx/Contents/MacOS/slic3r"
    elif sys.platform.startswith('linux'):
      osPath = "linux/bin/slic3r"
    else
      raise new Exception("Slicing is not supported on your OS.")
    
    #okay, send it back.
    return "%s/%s" % (self.config['slice_config']['engine']['path'], osPath)

  def slice(self):
    #create our command to do the slicing
    command = "%s --load %s --output %s --start-gcode %s --end-gcode %s %s" % (
      self.getSlicerPath,
      self.configFile.name,
      self.outFile.name,
      self.startFile.name,
      self.endFile.name,
      self.sliceFile.localPath
    )
    self.log.info("Running: %s" % command)
    
    #actually run our command
    
    #parse the results to get progress, and filament required
    
    #save all our results to an object
    result = hive.Object
    result.status = "success"
    result.output_file = self.outFile.name
    result.output_log = output_log
    
    return result