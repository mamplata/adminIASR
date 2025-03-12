// server.js
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { initializeNFC } = require("./utils/nfcHandler");

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

(async () => {
  try {
    console.log("✅ NFC Registration Server initialized...");
    initializeNFC(io);
  } catch (err) {
    console.error("❌ NFC Registration Server error:", err);
  }
})();

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`NFC Registration Server running on port ${PORT}`);
});
