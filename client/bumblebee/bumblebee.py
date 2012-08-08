#import WorkerBee
import botqueueapi
import workerbee

config = {
  'consumer_key' : '7f16659a9d83655c88e75e28b72223ca4e059b9b',
  'consumer_secret' : '78743f8a1c35830b80e724a87431be0c19812e3a',
  'token_key' : '86125f53cad61d22b8390d1daf52c3b563107852',
  'token_secret' : '087c013a0b4f72cdbaa8bd1535f296690f3037b9'
}

workerconfig = {
  'driver' : 'dummy'
}

wb = botqueueapi.BotQueueAPI(config['consumer_key'], config['consumer_secret'])
wb.setToken(config['token_key'], config['token_secret'])

worker = workerbee.WorkerBee(wb, workerconfig)
worker.run();
