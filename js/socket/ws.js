const Redis = require('ioredis');
const Server = require('socket.io');
const config = require('../config');

console.log(config);

let socket_port = 8080;
if (config.hasOwnProperty('SOCKET_PORT')) {
    socket_port = config.SOCKET_PORT;
}

let debug = false;
if (config.hasOwnProperty('APP_DEBUG')) {
    debug = config.APP_DEBUG;
}

let redis_host = '127.0.0.1';
let redis_port = '6379';
let redis_password = null;

if (config.hasOwnProperty('REDIS_HOST')) {
    redis_host = config.REDIS_HOST;
}

if (config.hasOwnProperty('REDIS_PORT')) {
    redis_port = config.REDIS_PORT;
}

if (config.hasOwnProperty('REDIS_PASSWORD')) {
    redis_password = config.REDIS_PASSWORD;
}

console.log(`Starting Socket IO server on port ${socket_port}`);

const io = Server(socket_port);
const redis = Redis({
    host: redis_host,
    port: redis_port,
    password: redis_password
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
            return;
        }

        console.log('Listening for redis events...');
    });
});