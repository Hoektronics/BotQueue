const SCWorker = require('socketcluster/scworker');

class Worker extends SCWorker {
    run() {
        console.log('   >> Worker PID:', process.pid);
        const scServer = this.scServer;
    }
}

new Worker();
