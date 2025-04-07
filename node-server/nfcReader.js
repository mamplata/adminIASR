// server.js (main server file)
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const {
  initializeNFCRead,
  updateActiveNfcReaders,
} = require("./utils/nfcRead");
const {
  checkScanners,
  setAssignment,
  removeAssignment,
  getAssignments,
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
    socket.join(deviceFingerprint);

    // When a client connects, sync its stored assignments.
    socket.on("syncScannerAssignments", (assignments) => {
      for (const uniqueKey in assignments) {
        setAssignment(deviceFingerprint, uniqueKey, assignments[uniqueKey]);
      }

      // Update active readers for this client.
      updateActiveNfcReaders(deviceFingerprint);
    });

    // Periodically check for connected scanners.
    setInterval(() => {
      const roles = getAssignments(deviceFingerprint);
      if (Object.keys(roles).length <= 2) {
        checkScanners(socket, deviceFingerprint);
      }
    }, 2000);
  } else {
    console.log("No device fingerprint set");
  }

  socket.on("assignRole", (data) => {
    const { uniqueKey, role } = data;
    const fp = uniqueKey.split("-port")[0];

    const roles = getAssignments(fp);
    if (Object.values(roles).includes(role)) {
      socket.emit("scannerAssignmentError", {
        uniqueKey,
        message: `Device already has a ${role} scanner assigned`,
      });
      return;
    }

    setAssignment(fp, uniqueKey, role);
    socket.emit("scannerAssigned", { uniqueKey, role });
    updateActiveNfcReaders(fp);
  });

  socket.on("removeAssignment", (data) => {
    const { uniqueKey } = data;
    const fp = uniqueKey.split("-port")[0];
    const roles = getAssignments(fp);
    if (!roles[uniqueKey]) {
      socket.emit("scannerAssignmentError", {
        uniqueKey,
        message: "No assignment exists for this scanner",
      });
      return;
    }
    removeAssignment(fp, uniqueKey);
    socket.emit("scannerRemoved", { uniqueKey });
    console.log("Removed assignment:", uniqueKey);
  });
});

initializeNFCRead(io);

const PORT = 4000;
server.listen(PORT, () => {
  console.log(`NFC Read Server running on port ${PORT}`);
});
