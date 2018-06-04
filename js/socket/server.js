/*
  This is the SocketCluster master controller file.
  It is responsible for bootstrapping the SocketCluster master process.
  Be careful when modifying the options object below.
  If you plan to run SCC on Kubernetes or another orchestrator at some point
  in the future, avoid changing the environment variable names below as
  each one has a specific meaning within the SC ecosystem.
*/

const fs = require("fs");
const config = require("../config");
const path = require("path");

const fsUtil = require("socketcluster/fsutil");
const waitForFile = fsUtil.waitForFile;

const SocketCluster = require("socketcluster");

const workerControllerPath = path.join(__dirname, "worker.js");
const brokerControllerPath = path.join(__dirname, "broker.js");
const environment = "prod";

const publicKey = fs.readFileSync(path.join(__dirname, "../../storage/oauth-public.key"));
const privateKey = fs.readFileSync(path.join(__dirname, "../../storage/oauth-private.key"));

const options = {
    path: "/socket",
    workers: config.SOCKET_WORKERS,
    brokers: config.SOCKET_BROKERS,
    port: config.SOCKET_PORT,
    authPublicKey: publicKey,
    authPrivateKey: privateKey,

    // If your system doesn"t support "uws", you can switch to "ws" (which is slower but works on older systems).
    wsEngine: "ws",
    appName: config.APP_NAME,
    workerController: workerControllerPath,
    brokerController: brokerControllerPath,
    workerClusterController: null,
    socketChannelLimit: 1000,
    clusterStateServerHost: null, // process.env.SCC_STATE_SERVER_HOST || null,
    clusterStateServerPort: null, // process.env.SCC_STATE_SERVER_PORT || null,
    clusterAuthKey: null, // process.env.SCC_AUTH_KEY || null,
    clusterInstanceIp: null, // process.env.SCC_INSTANCE_IP || null,
    clusterInstanceIpFamily: null, // process.env.SCC_INSTANCE_IP_FAMILY || null,
    clusterStateServerConnectTimeout: null, // Number(process.env.SCC_STATE_SERVER_CONNECT_TIMEOUT) || null,
    clusterStateServerAckTimeout: null, // Number(process.env.SCC_STATE_SERVER_ACK_TIMEOUT) || null,
    clusterStateServerReconnectRandomness: null, // Number(process.env.SCC_STATE_SERVER_RECONNECT_RANDOMNESS) || null,
    crashWorkerOnError: true,
    // If using nodemon, set this to true, and make sure that environment is "dev".
    killMasterOnSignal: false,
    environment: environment
};

const bootTimeout = 10000;

const start = function () {
    const socketCluster = new SocketCluster(options);

    socketCluster.on(socketCluster.EVENT_WORKER_CLUSTER_START, function (workerClusterInfo) {
        console.log("   >> WorkerCluster PID:", workerClusterInfo.pid);
    });
};

const bootCheckInterval = 200;
const bootStartTime = Date.now();

// Detect when Docker volumes are ready.
const startWhenFileIsReady = (filePath) => {
    const errorMessage = `Failed to locate a controller file at path ${filePath} ` +
        `before SOCKETCLUSTER_CONTROLLER_BOOT_TIMEOUT`;

    return waitForFile(filePath, bootCheckInterval, bootStartTime, bootTimeout, errorMessage);
};

const filesReadyPromises = [
    startWhenFileIsReady(workerControllerPath),
    startWhenFileIsReady(brokerControllerPath)
];
Promise.all(filesReadyPromises)
.then(() => {
  start();
})
.catch((err) => {
  console.error(err.stack);
  process.exit(1);
});
