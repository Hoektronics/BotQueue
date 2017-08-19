const Redis = require('ioredis');
const Server = require('socket.io');
const config = require('../config');

if (config.APP_DEBUG) {
    console.log('Application is in debug mode');
    console.log('Events will be printed to the screen');
}

console.log(`Starting Socket IO server on port ${config.SOCKET_PORT}`);

const io = Server(config.SOCKET_PORT);
const redis = Redis({
    host: config.REDIS_HOST,
    port: config.REDIS_PORT,
    password: config.REDIS_PASSWORD
});

// TODO Setup io.use middleware for auth

io.on('connection', (socket) => {
    if(debug) {
        console.log(`Client connected with id ${socket.id}`);
    }

    redis.on('pmessage', (pattern, channel, message) =>{
        message = JSON.parse(message);

        if(debug) {
            console.log(`Channel: ${channel}`);
            console.log(`Event: ${message.event}`);
        }

        // TODO Emit only to the correct room channel
        io.emit(message.event, message.data);
    });

    redis.psubscribe('*', (err, count) => {
        if(err) {
            console.log('Redis could not subscribe.');
            process.exit(1);
        }

        console.log('Listening for redis events...');
    });
});