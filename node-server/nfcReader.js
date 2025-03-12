// nfcReadServer.js
const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { initializeNFCRead } = require("./utils/nfcRead");

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
        console.log("✅ NFC Read Server initialized...");
        initializeNFCRead(io);
    } catch (err) {
        console.error("❌ NFC Read Server error:", err);
    }
})();

const PORT = 4000;
server.listen(PORT, () => {
    console.log(`NFC Read Server running on port ${PORT}`);
});
