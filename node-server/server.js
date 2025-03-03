// server.js
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const os = require("os");
const crypto = require("crypto");
const { machineIdSync } = require("node-machine-id");
const macaddress = require("macaddress");
const si = require("systeminformation");

// Import your custom utilities
const { generateFingerprint } = require("./utils/fingerprint");
const { initializeNFC, pendingStudent } = require("./utils/nfcHandler");

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: ["http://localhost:8000", "http://127.0.0.1:8000"],
    methods: ["GET", "POST"],
    allowedHeaders: ["Content-Type"],
    credentials: true,
  },
});

// Always initialize NFC scanning, no device checking required.
(async () => {
  try {

    console.log("✅ NFC scanning enabled...");
    initializeNFC(io);
  } catch (err) {
    console.error("❌ Initialization error:", err);
  }
})();

// Function to generate detailed fingerprint info (for getDeviceInfo event)
async function generateFingerprintDetails() {
  try {
    const machineId = machineIdSync({ original: true });
    const macAddress = await macaddress.one();
    const uuidData = await si.uuid();
    const hardwareUUID = uuidData.hardware || '';
    const deviceName = os.hostname();
    const combinedData = machineId + hardwareUUID + macAddress;
    const deviceFingerprint = crypto
      .createHash("sha256")
      .update(combinedData)
      .digest("hex");

    return { machineId, hardwareUUID, macAddress, deviceFingerprint, deviceName };
  } catch (error) {
    throw new Error("Error generating fingerprint details: " + error);
  }
}

// Set up Socket.io connections and events
io.on("connection", (socket) => {
  console.log(`User connected: ${socket.id}`);

  // NFC related event: student registration
  socket.on("registerStudent", (studentID) => {
    console.log(`Student ID received: ${studentID}`);
    pendingStudent.id = studentID;
    socket.emit("nfcStatus", "Tap your NFC card now!");
  });

  // Device info event: return detailed fingerprint info
  socket.on("getDeviceInfo", async () => {
    try {
      const details = await generateFingerprintDetails();
      console.log("Device details:", details);
      socket.emit("deviceInfo", details);
    } catch (error) {
      socket.emit("error", error.message);
    }
  });

  socket.on("disconnect", () => {
    console.log(`User disconnected: ${socket.id}`);
  });
});

// Start the server
const PORT = 3000;
server.listen(PORT, () => {
  console.log(`Socket.io server running on port ${PORT}`);
});
