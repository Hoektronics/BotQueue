const SCWorker = require('socketcluster/scworker');

class Worker extends SCWorker {
    run() {
        console.log('   >> Worker PID:', process.pid);
        const scServer = this.scServer;

        /*
          In here we handle our incoming realtime connections and listen for events.
        */
        scServer.on('connection', function (socket) {
            console.log("Connection made!");
            console.log(socket);
        });
    }
}

new Worker();
