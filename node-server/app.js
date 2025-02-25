const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const path = require("path");

// Modules for generating device fingerprint
const { machineIdSync } = require('node-machine-id');
const macaddress = require('macaddress');
const si = require('systeminformation');
const crypto = require('crypto');
// Import NFC module (for ACR122U scanner, for example)
const { NFC } = require("nfc-pcsc");

const app = express();
const server = http.createServer(app);

// Configure socket.io (adjust the origin to match your front-end)
const io = socketIo(server, {
  cors: {
    origin: "http://localhost:5173",
    methods: ["GET", "POST"],
    allowedHeaders: ["Content-Type"],
    credentials: true,
  },
});

// Serve static files (your Vue.js app or other front-end)
app.use(express.static(path.join(__dirname, "public")));

// Start the HTTP server
server.listen(3000, () => {
  console.log("Server running on http://localhost:3000");
});

/**
 * generateFingerprint()
 * Combines hardware identifiers (machine ID, MAC address, and hardware UUID)
 * and returns a SHA-256 hash as a unique, one-way fingerprint.
 */
async function generateFingerprint() {
  // Get OS machine ID (original value)
  const machineId = machineIdSync({ original: true });
  
  // Get the primary MAC address from the first non-internal interface
  const mac = await macaddress.one();
  
  // Get the hardware UUID (if available)
  const uuidData = await si.uuid();
  const hardwareUUID = uuidData.hardware || '';
  
  // Combine the values and create a SHA-256 hash
  const combined = machineId + mac + hardwareUUID;
  return crypto.createHash('sha256').update(combined).digest('hex');
}

/**
 * initializeNFC()
 * Sets up the NFC reader using nfc-pcsc.
 * When a card is detected, its UID is emitted to connected clients via socket.io.
 */
function initializeNFC() {
  const nfc = new NFC();

  nfc.on("reader", (reader) => {
    console.log(`Reader detected: ${reader.reader.name}`);
    
    // When a card is detected
    reader.on("card", (card) => {
      console.log(`Card detected: ${card.uid}`);
      // Emit the UID to all connected clients
      io.emit("newCard", card.uid);
    });

    // Log card removal events
    reader.on("card.off", (card) => {
      console.log(`Card removed: ${card.uid}`);
    });

    reader.on("error", (err) => {
      console.error(`Error (${reader.reader.name}):`, err);
    });

    reader.on("end", () => {
      console.log("Reader disconnected");
    });
  });

  nfc.on("error", (err) => {
    console.error("NFC Error:", err);
  });
}

// Immediately-invoked async function to check fingerprint on server load.
(async () => {
  try {
    const deviceFingerprint = await generateFingerprint();
    console.log("Generated Device Fingerprint:", deviceFingerprint);

    // Set your registered fingerprint here (the one stored upon device registration)
    const registeredFingerprint = 'acf0bc60bf7bef30419eb82d41cf0ba8eca8050ef9b3f1ab3bbee7d02a436122'; // replace with your actual value

    // Compare the generated fingerprint with the registered fingerprint.
    if (deviceFingerprint !== registeredFingerprint) {
      console.error("Device not registered. Aborting NFC scanning.");
      return; // NFC scanning will not be initialized
    }

    console.log("Device is registered. Enabling NFC scanning...");
    initializeNFC();
  } catch (err) {
    console.error("Initialization error:", err);
  }
})();
