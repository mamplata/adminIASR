// server.js
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
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

// In-memory store for registered devices
const registeredDevices = new Map();

io.on("connection", (socket) => {
  console.log(`Client connected: ${socket.id}`);

  // Device registration event
  socket.on("register", (deviceName) => {
    if (!deviceName) {
      socket.emit("registrationError", "Device name is required.");
      return;
    }
    // Generate a unique token
    const token = crypto.randomBytes(16).toString("hex");
    // Store the token along with device info and registration time
    registeredDevices.set(token, { deviceName, registeredAt: new Date() });
    // Send the token back to the client
    socket.emit("registrationSuccess", token);
    console.log(`Device '${deviceName}' registered with token: ${token}`);
  });

  // Protected event that requires a valid token
  socket.on("protectedEvent", (data) => {
    const token = data.token;
    if (!token || !registeredDevices.has(token)) {
      socket.emit("error", "Access denied: Unrecognized device");
      return;
    }
    // Process the event for a recognized device
    socket.emit("protectedResponse", "Access granted to protected resource");
  });

  socket.on("disconnect", () => {
    console.log(`Client disconnected: ${socket.id}`);
  });
});

server.listen(3000, () => {
  console.log("Socket.io server running on http://localhost:3000");
});
