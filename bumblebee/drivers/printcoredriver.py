import bumbledriver
import printcore

#todo: this whole thing sucks.  we need a much better way to interface with this.
class printcoredriver(bumbledriver.bumbledriver):
  def __init__(self, config):
    super(printcoredriver, self).__init__(config)
    
    self.p = printcore(config['port'],config['baud'])
    self.p.loud = True

  def startPrint(self, jobfile, filesize):
    p.startprint(jobfile)
    self.filesize = filesize

  #this doesn't do much, just a thread to watch our thread indirectly.
  def executeFile(self):
    try:
      while(p.printing):
        time.sleep(1)
    except Exception as ex:
      
    finally:
      p.disconnect()
    
  def getPercentage(self):
    return p.getPercentage()
    
  def pause(self):
    self.p.pause()
    super(printcoredriver, self).pause()
    
  def resume(self):
    self.p.resume()
    super(printcoredriver, self).resume()

  def connect(self):
    self.p.connect()
    super(printcoredriver, self).connect()
    
  def disconnect(self):
    self.p.disconnect()
    super(printcoredriver, self).disconnect()