const SCBroker = require('socketcluster/scbroker');
const scClusterBrokerClient = require('scc-broker-client');
const config = require('../config');
const Redis = require('ioredis');

const subClient = Redis({
    host: config.REDIS_HOST,
    port: config.REDIS_PORT,
    password: config.REDIS_PASSWORD
});

class Broker extends SCBroker {
    run() {
        let broker = this;
        console.log('   >> Broker PID:', process.pid);

        // This is defined in server.js (taken from environment variable SC_CLUSTER_STATE_SERVER_HOST).
        // If this property is defined, the broker will try to attach itself to the SC cluster for
        // automatic horizontal scalability.
        // This is mostly intended for the Kubernetes deployment of SocketCluster - In this case,
        // The clustering/sharding all happens automatically.

        if (broker.options.clusterStateServerHost) {
            scClusterBrokerClient.attach(broker, {
                stateServerHost: broker.options.clusterStateServerHost,
                stateServerPort: broker.options.clusterStateServerPort,
                authKey: broker.options.clusterAuthKey,
                stateServerConnectTimeout: broker.options.clusterStateServerConnectTimeout,
                stateServerAckTimeout: broker.options.clusterStateServerAckTimeout,
                stateServerReconnectRandomness: broker.options.clusterStateServerReconnectRandomness
            });
        }

        broker.on('subscribe', function (channel) {
            console.log(`Channel ${channel} subscribed to`);
            subClient.subscribe(channel);
        });

        broker.on('unsubscribe', function (channel) {
            console.log(`Channel ${channel} unsubscribed from`);
            subClient.unsubscribe(channel);
        });

        const instanceIdRegex = /^[^\/]*\//;

        subClient.on('message', function(channel, message) {
            let sender = null;

            message = message.replace(instanceIdRegex, function(match) {
                sender = match.slice(0, -1);
                return '';
            });

            // Do not publish if this message was published by
            // the current SC instance since it has already been
            // handled internally
            if (sender == null || sender !== broker.instanceId) {
                const type = message.charAt(0);
                let data = message.slice(2);

                console.log(`Publishing ${data} to ${channel}`);

                if(type === 'o') {
                    try {
                        data = JSON.parse(data);
                    } catch (e) {}
                }

                broker.publish(channel, data);
            }
        });
    }
}

new Broker();
