const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { initializeNFCRead } = require("./utils/nfcRead");
const {
    checkScanners,
    isPortAssigned,
    getPortFromUniqueKey,
    saveScannerRoles,
} = require("./utils/port");
let {
    scannerRoles,
} = require("./utils/port");

require("dotenv").config();

const app = express();
const server = http.createServer(app);
const allowedOrigins = process.env.ALLOWED_ORIGINS.split(",");

const io = new Server(server, {
    cors: {
        origin: allowedOrigins,
        methods: ["GET", "POST"],
        allowedHeaders: ["Content-Type"],
        credentials: true,
    },
});

io.on("connection", (socket) => {
    const deviceFingerprint = socket.handshake.query.deviceFingerprint;
    console.log("Connected device fingerprint:", deviceFingerprint);

    if (deviceFingerprint) {
        setInterval(() => {
            checkScanners(socket, deviceFingerprint);
        }, 2000);
    } else {
        console.log("No device fingerprint set");
    }

    console.log(scannerRoles);

    socket.on('assignRole', (data) => {
        const { uniqueKey, role } = data;
        const portPath = getPortFromUniqueKey(uniqueKey);
        if (scannerRoles[uniqueKey]) {
            socket.emit('scannerAssignmentError', { uniqueKey, message: "Scanner already assigned" });
        } else if (isPortAssigned(portPath)) {
            socket.emit('scannerAssignmentError', { uniqueKey, message: `Port ${portPath} already assigned` });
        } else {
            scannerRoles[uniqueKey] = role;
            saveScannerRoles();
            socket.emit('scannerAssigned', { uniqueKey, role });
        }
    });
});

initializeNFCRead(io);

const PORT = 4000;
server.listen(PORT, () => {
    console.log(`NFC Read Server running on port ${PORT}`);
});
