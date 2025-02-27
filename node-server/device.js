const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { machineIdSync } = require("node-machine-id");
const macaddress = require("macaddress");
const si = require("systeminformation");
const crypto = require("crypto");
const os = require("os"); // Import the OS module

async function generateFingerprintDetails() {
  try {
    // Get the machine ID
    const machineId = machineIdSync({ original: true });
    const macAddress = await macaddress.one();
    const uuidData = await si.uuid();
    const hardwareUUID = uuidData.hardware || '';

    // Get device name using os module
    const deviceName = os.hostname();

    // Create a fingerprint by hashing the concatenated string
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

const app = express();
const server = http.createServer(app);
// Configure socket.io (adjust the origin to match your front-end)
const io = new Server(server, {
  cors: {
    origin: ["http://localhost:8000", "http://127.0.0.1:8000"],
    methods: ["GET", "POST"],
    allowedHeaders: ["Content-Type"],
    credentials: true,
  },
});

// Listen for socket connections
io.on("connection", (socket) => {
  console.log("New client connected");

  // Listen for the event from the client
  socket.on("getDeviceInfo", async () => {
    try {
      const details = await generateFingerprintDetails();
      console.log(details);
      // Emit the details back to the client
      socket.emit("deviceInfo", details);
    } catch (error) {
      socket.emit("error", error.message);
    }
  });

  socket.on("disconnect", () => {
    console.log("Client disconnected");
  });
});

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`Server listening on port ${PORT}`);
});
