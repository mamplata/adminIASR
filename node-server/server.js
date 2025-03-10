// server.js
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

const { initializeNFC } = require("./utils/nfcHandler");
require("dotenv").config();

const app = express();
const server = http.createServer(app);
// Split the ALLOWED_ORIGINS string into an array.
const allowedOrigins = process.env.ALLOWED_ORIGINS.split(",");

const io = new Server(server, {
  cors: {
    origin: allowedOrigins,
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

// Start the server
const PORT = 3000;
server.listen(PORT, () => {
  console.log(`Socket.io server running on port ${PORT}`);
});
