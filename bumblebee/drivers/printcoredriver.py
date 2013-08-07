import bumbledriver
import printcore
import time
import logging
from threading import Thread

def scanPorts():
  try:
    import serial.tools.list_ports
    return serial.tools.list_ports.comports()
  except Exception as ex:
    self.log = logging.getLogger('botqueue')
    self.log.error("Printcore cannot scan serial ports.")
    self.log.exception(ex)
    return None

#todo: this whole thing sucks.  we need a much better way to interface with this.
class printcoredriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    super(printcoredriver, self).__init__(config)
    self.log = logging.getLogger('botqueue')
    self.printThread = False
    
  def startPrint(self, jobfile):
    self.p = printcore.printcore()
    self.p.loud = False
    try:
      self.printing=True
      self.connect()
      while not self.isConnected():
        time.sleep(1)
        self.log.debug("Waiting for driver to connect.")
      self.p.startprint(jobfile.localFile)
      self.printThread = Thread(target=self.printThreadEntry).start()
    except Exception as ex:
      self.log.error("Error starting print: %s" % ex)
      self.disconnect()
      raise ex

  #this doesn't do much, just a thread to watch our thread indirectly.
  def executeFile(self):
    while(self.p.printing):
      self.printing = self.p.printing

      self.error = self.p.error
      self.errorMessage = self.p.errorMessage
      if self.error:
        self.disconnect()
        raise Exception(self.errorMessage)

      time.sleep(0.1)
    time.sleep(1)
    self.disconnect()

  def getPercentage(self):
    return self.p.get_percentage()
    
  def pause(self):
    self.p.pause()
    super(printcoredriver, self).pause()
    
  def resume(self):
    self.p.resume()
    super(printcoredriver, self).resume()

  def reset(self):
    self.p.reset()
    super(printcoredriver, self).reset()

  def isConnected(self):
    return self.p.online and self.p.printer

  def stop(self):
    try:
      self.p.stop()
      self.disconnect()
    except AttributeError as ex:
      self.log.error(ex)
  
  def getTemperature(self):
    return self.p.get_temperatures()
  
  def connect(self):
    if not self.isConnected():
      self.p.connect(self.config['port'], self.config['baud'])
      super(printcoredriver, self).connect()
    
  def disconnect(self):
    if self.isConnected():
      if self.printThread:
        self.printThread.join(10)
      self.p.disconnect()
      super(printcoredriver, self).disconnect()
