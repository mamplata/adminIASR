const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { NFC } = require("nfc-pcsc");
const { machineIdSync } = require("node-machine-id");
const macaddress = require("macaddress");
const si = require("systeminformation");
const crypto = require("crypto");

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

let nfcReader = null;
let pendingStudentID = null;
const registeredStudents = [];

/**
 * generateFingerprint()
 * Creates a unique fingerprint by combining hardware details.
 */
async function generateFingerprint() {
  try {
    const machineId = machineIdSync({ original: true });
    const mac = await macaddress.one();
    const uuidData = await si.uuid();
    const hardwareUUID = uuidData.hardware || '';

    const combined = machineId + mac + hardwareUUID;
    return crypto.createHash('sha256').update(combined).digest('hex');
  } catch (err) {
    console.error("❌ Error generating fingerprint:", err);
    return null;
  }
}

/**
 * initializeNFC()
 * Starts the NFC reader if the device is registered.
 */
function initializeNFC() {
  const nfc = new NFC();

  nfc.on("reader", (reader) => {
    console.log(`NFC Reader detected: ${reader.name}`);
    nfcReader = reader;

    reader.on("card", async (card) => {
      if (!pendingStudentID) {
        console.log("No Student ID provided before scanning.");
        return;
      }

      console.log(`Card detected for Student ID ${pendingStudentID}: ${card.uid}`);

      const block = 4; // Data storage block
      const sectorTrailerBlock = 7; // Sector Trailer (controls access to Block 4)
      const keyType = 0x60; // Key A
      const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Default Key A
      const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]); // Custom Key A
      const semesterData = "2ndsem_2024-2025"; // Data to be written

      try {
        // Authenticate before writing using Default Key A
        await reader.authenticate(block, keyType, defaultKey);

        // Prepare data buffer (16 bytes)
        let dataBuffer = Buffer.alloc(16, 0); // Initialize empty buffer
        dataBuffer.write(semesterData, 0, "utf-8"); // Write data

        // Write the data to Block 4
        await reader.write(block, dataBuffer, 16);
        console.log(`✅ Successfully wrote "${semesterData}" to Block ${block}`);

        // 🔐 Now Lock Block 4 by changing the Key A to a custom key
        await reader.authenticate(sectorTrailerBlock, keyType, defaultKey);

        // Set new access bits: Readable by all, Write requires Custom Key A
        const accessBits = Buffer.from([0x7F, 0x07, 0x88, 0x40]);

        const sectorTrailerNewKey = Buffer.concat([
          customKey,  // New Key A (Custom Key)
          accessBits, // Access Bits
          customKey   // New Key B (same as Key A)
        ]);

        // Write new keys to the sector trailer
        await reader.write(sectorTrailerBlock, sectorTrailerNewKey, 16);
        console.log("🔒 Block 4 is now locked with a custom key!");

        // Save the Student ID and NFC UID
        const studentRecord = { studentID: pendingStudentID, nfcUID: card.uid };
        registeredStudents.push(studentRecord);

        // Emit updated student list to all clients
        io.emit("studentRegistered", registeredStudents);

        // Reset pending Student ID
        pendingStudentID = null;
      } catch (err) {
        console.error("❌ Error writing to NFC tag:", err.message);
      }
    });

    reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
    reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
  });

  nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));
}

// Check device fingerprint before initializing NFC
(async () => {
  try {
    const deviceFingerprint = await generateFingerprint();
    console.log("Generated Device Fingerprint:", deviceFingerprint);

    // Replace with your stored registered fingerprint
    const registeredFingerprint = '00d499bcd9327060caec1ebdf57e09845e1442f1bd82107027788d9d9d145b0e';

    if (deviceFingerprint !== registeredFingerprint) {
      console.error("❌ Device not registered. Blocking NFC access.");
      return; // Stop execution to prevent NFC initialization
    }

    console.log("✅ Device is registered. Enabling NFC scanning...");
    initializeNFC();
  } catch (err) {
    console.error("❌ Initialization error:", err);
  }
})();

// Socket.io setup
io.on("connection", (socket) => {
  console.log(`User connected: ${socket.id}`);

  socket.on("registerStudent", (studentID) => {
    console.log(`Student ID received: ${studentID}`);
    pendingStudentID = studentID;
    socket.emit("nfcStatus", "Tap your NFC card now!");
  });

  socket.on("disconnect", () => {
    console.log(`User disconnected: ${socket.id}`);
  });
});

// Start the server
server.listen(3000, () => {
  console.log("Socket.io server running on port 3000");
});
