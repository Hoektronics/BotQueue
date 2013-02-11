import os, sys
import bumbledriver
import logging
import time
from threading import Thread, Lock
import re

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
    self.progressLock = Lock()
    self.s3g = False
    
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

    with self.progressLock:
      self.currentPosition = 0

    #turn our leds on white
    self.s3g.set_RGB_LED(255, 255, 255, 0);

    # our start gcode
    # if self.start_gcode:
    #   for line in self.start_gcode:
    #     self.parser.execute_line(line)

    #load our file into memory.
    lines = []
    while 1:
      line = self.jobfile.localFile.readline()
      if not line:
        break
      lines.append(line)

    comment = re.compile('(;.*)')
    comment2 = re.compile('(\(.*\))')

    #create new lines
    lines = self.sp.process_gcode(lines)
    for line in lines:
      line = comment.sub('', line)
      line = comment2.sub('', line)
      line = line.rstrip()
      if line:
        self.log.debug(line)
        try:
          self.parser.execute_line(line)
        except Exception as ex:
          self.log.debug(ex)
      
      #with self.progressLock:
      self.currentPosition = self.currentPosition + len(line)

    #our end gcode
    # if self.end_gcode:
    #   for line in self.end_gcode:
    #     self.parser.execute_line(line)
      
  def getPercentage(self):
    with self.progressLock:
      return float(self.currentPosition) / float(self.jobfile.localSize)*100

  def connect(self):
    if not self.isConnected():
      #load our config and connect.
      factory = makerbot_driver.MachineFactory()

      try:
        obj = factory.build_from_port(self.config['port'])
        self.s3g = obj.s3g

        #create our start and end sequences.
        self.assembler = makerbot_driver.GcodeAssembler(getattr(obj, 'profile'))
        start, end, variables = self.assembler.assemble_recipe()
        #self.start_gcode = self.assembler.assemble_start_sequence(start)
        #self.end_gcode = self.assembler.assemble_end_sequence(end)
        self.log.debug(self.assembler.assemble_start_sequence(start))
        self.assembler.assemble_end_sequence(end)
        
        #extra crap for parsing gcode.
        self.sp = makerbot_driver.GcodeProcessors.SlicerProcessor()
        self.parser = getattr(obj, 'gcodeparser')
        self.parser.environment.update(variables)
        super(s3gdriver, self).connect()

      except serial.SerialException as ex:
        self.s3g = False
        raise Exception(ex.message)

  def isConnected(self):
    if self.s3g:
      return self.s3g.is_open()
    else:
      return False
      
  def disconnect(self):
    if self.isConnected():
      self.s3g.close()
      super(s3gdriver, self).disconnect()