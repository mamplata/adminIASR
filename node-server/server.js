// server.js
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

const { initializeNFC } = require("./utils/nfcHandler");

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: [
      "http://localhost:8000",
      "http://127.0.0.1:8000",
      "http://localhost:5173",
      "http://127.0.0.1:5173",
    ],
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
