const SCWorker = require('socketcluster/scworker');
const request = require('request');
let url = require('url');

class Worker extends SCWorker {
    run() {
        console.log('   >> Worker PID:', process.pid);
        const scServer = this.scServer;
        const self = this;

        scServer.addMiddleware(scServer.MIDDLEWARE_SUBSCRIBE,
            function (req, next) {
                console.log("Subscription attempted!");
                if (req.authTokenExpiredError) {
                    next(req.authTokenExpiredError); // Fail with a default auth token expiry error
                } else {
                    self.attemptAuth(req, next);
                }
            }
        );
    }

    attemptAuth(req, next) {
        let options = {
            url: this.authHost(req.socket) + "/broadcasting/auth",
            json: { channel_name: req.channel },
            headers: {
                Authorization: 'Bearer ' + req.socket.signedAuthToken
            },
            rejectUnauthorized: false
        };

        this.makeRequest(req.socket, options, next);
    }

    authHost(socket) {
        let authHostSelected = 'http://localhost';

        if(socket.request.headers.referer) {
            let referer = url.parse(socket.request.headers.referer);

            for (let authHost of authHosts) {
                authHostSelected = authHost;

                if (Worker.hasMatchingHost(referer, authHost)) {
                    authHostSelected = `${referer.protocol}//${referer.host}`;
                    break;
                }
            }
        }

        return authHostSelected;
    }

    static hasMatchingHost(referer, host) {
        return referer.hostname.substr(referer.hostname.indexOf('.')) === host ||
            `${referer.protocol}//${referer.host}` === host ||
            referer.host === host;
    }

    makeRequest(socket, options, next) {
        request.post(options, (error, response) => {
            if (error) {
                console.log(`[${new Date().toLocaleTimeString()}] - Error authenticating ${socket.id} for ${options.json.channel_name}`);

                console.log(error);

                let err = new Error("Could not verify authentication");
                next(err);
            } else if (response.statusCode !== 200) {
                console.log(`[${new Date().toLocaleTimeString()}] - ${socket.id} could not be authenticated to ${options.json.channel_name}`);
                console.log(response.body);

                let err = new Error("Could not verify authentication");
                next(err);
            } else {
                console.log(`[${new Date().toLocaleTimeString()}] - ${socket.id} authenticated for: ${options.json.channel_name}`);

                next()
            }
        });
    }
}

new Worker();
