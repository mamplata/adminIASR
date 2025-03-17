// server.js (or your main server file)
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { initializeNFCRead } = require("./utils/nfcRead");
const {
    checkScanners,
    isPortAssigned,
    getPortFromUniqueKey,
    saveScannerRoles,
    getScannerRoles,
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

    socket.on("assignRole", (data) => {
        const { uniqueKey, role } = data;
        const roles = getScannerRoles();
        const portPath = getPortFromUniqueKey(uniqueKey);

        // If this exact device (unique key) is already assigned, do nothing.
        if (roles[uniqueKey]) {
            socket.emit("scannerAssignmentError", {
                uniqueKey,
                message: "Scanner already assigned",
            });
            return;
        }

        // For a new scanner on a different port:
        // If the same role is already assigned to any device on a different port,
        // remove that assignment to allow the new scanner to override it.
        Object.keys(roles).forEach((key) => {
            if (roles[key] === role && getPortFromUniqueKey(key) !== portPath) {
                delete roles[key];
            }
        });

        // Assign the role to the new scanner.
        roles[uniqueKey] = role;
        saveScannerRoles(roles);
        socket.emit("scannerAssigned", { uniqueKey, role });
    });
});

initializeNFCRead(io);

const PORT = 4000;
server.listen(PORT, () => {
    console.log(`NFC Read Server running on port ${PORT}`);
});
