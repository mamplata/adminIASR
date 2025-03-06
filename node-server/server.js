// server.js
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

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

// Set up Socket.io connections and events
io.on("connection", (socket) => {
  console.log(`User connected: ${socket.id}`);

  // NFC related event: student registration
  socket.on("registerStudent", (studentID) => {
    console.log(`Student ID received: ${studentID}`);
    pendingStudent.id = studentID;
    socket.emit("nfcStatus", "Tap your NFC card now!");
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
