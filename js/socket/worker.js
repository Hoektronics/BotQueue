const path = require('path');
const SCWorker = require('socketcluster/scworker');
const jwt = require('jsonwebtoken');
const fs = require('fs');

class Worker extends SCWorker {
    run() {
        console.log('   >> Worker PID:', process.pid);
        const scServer = this.scServer;

        const cert = fs.readFileSync(path.join(__dirname, '../../storage/oauth-public.key'));

        scServer.on('connection', function(socket) {
            socket.on('auth', function(data, res) {
                const token = data['token'];

                try {
                    jwt.verify(token, cert);

                    const tokenData = {
                        token: token
                    };

                    socket.emit('#setAuthToken', tokenData);

                    res(null, "authenticated")
                } catch(err) {
                    res(err, "Could not verify token!");
                }
            })
        });

        scServer.addMiddleware(scServer.MIDDLEWARE_SUBSCRIBE,
            function (req, next) {
                console.log("Subscription attempted!");
                if (req.authTokenExpiredError) {
                    next(req.authTokenExpiredError); // Fail with a default auth token expiry error
                } else {
                    next(); // Allow
                }
            }
        );
    }
}

new Worker();
