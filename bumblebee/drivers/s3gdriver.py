import os, sys
import bumbledriver
import logging
import time
from threading import Thread

#goddamn ugly makerbot code.
lib_path = os.path.abspath('./drivers')
sys.path.append(lib_path)

#this has to come after the above code
import makerbot_driver
import serial

class s3gdriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    super(s3gdriver, self).__init__(config)

    self.log = logging.getLogger('botqueue')
    
    #load our config and connect.
    factory = makerbot_driver.MachineFactory()
    obj = factory.build_from_port(self.config['port'])
    self.s3g = obj.s3g
    
    #create our start and end sequences.
    self.assembler = makerbot_driver.GcodeAssembler(getattr(obj, 'profile'))
    start, end, variables = self.assembler.assemble_recipe()
    self.start_gcode = self.assembler.assemble_start_sequence(start)
    self.end_gcode = self.assembler.assemble_end_sequence(end)

    #extra crap for parsing gcode.
    self.sp = makerbot_driver.GcodeProcessors.SlicerProcessor()
    self.parser = getattr(obj, 'gcodeparser')
    self.parser.environment.update(variables)
    
  def startPrint(self, jobfile):
    try:
      self.jobfile = jobfile
      #parser.state.values["build_name"] = jobfile.localFile.localPath[:15]
      self.parser.state.values["build_name"] = "BOTQUEUE!"
      self.printing=True
      self.connect()
      while not self.isConnected():
        time.sleep(1)
        self.log.debug("Waiting for driver to connect.")
      Thread(target=self.printThreadEntry).start()
    except Exception as ex:
      self.log.error("Error starting print: %s" % ex)
      raise ex
        
  #this doesn't do much, just a thread to watch our thread indirectly.
  def executeFile(self):

    self.currentPosition = 0

    #turn our leds on white
    self.s3g.set_RGB_LED(255, 255, 255, 0);

    # our start gcode
    for line in self.start_gcode:
      self.parser.execute_line(line)

    #load our file into memory.
    lines = []
    while 1:
      line = self.jobfile.localFile.readline()
      if not line:
        break
      lines.append(line)

    #create new lines
    lines = self.sp.process_gcode(lines)
    for line in lines:
      self.parser.execute_line(line)
      self.currentPosition = self.currentPosition + len(line)

    #our end gcode
    for line in self.end_gcode:
      self.parser.execute_line(line)
      
  def getPercentage(self):
    return float(self.currentPosition) / float(self.jobfile.localSize)*100

  def connect(self):
    if not self.isConnected():
      self.s3g.open()
      super(s3gdriver, self).connect()

  def isConnected(self):
    return self.s3g.is_open()
                    
  def disconnect(self):
    self.s3g.close()
    super(s3gdriver, self).disconnect()