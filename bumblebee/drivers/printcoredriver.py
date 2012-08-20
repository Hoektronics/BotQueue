import bumbledriver
import printcore

#todo: this whole thing sucks.  we need a much better way to interface with this.
class printcoredriver(BumbleDriver):
  def __init__(self, config):
    #super(PrintcoreDriver, self).__init__(config)
    self.config = config
    self.connected = False
    
    self.p = printcore(config['port'],config['baud'])
    self.p.loud = True

  def execute(self, line):
    pass
  # def executeGCodeFile(self, path):
  #   #TODO umm... does this open the entire file into memory?  that would be retardiculous.
  #   gcode=[i.replace("\n","") for i in open(path)]
  #   self.p.startprint(gcode)
  # 
  #   try:
  #     while(self.p.printing):
  #       time.sleep(1)
  #       self.updatePercentage(100*float(p.queueindex)/len(p.mainqueue))
  #   finally:
  #     self.p.disconnect()    