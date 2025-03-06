const { NFC } = require("nfc-pcsc");
const crypto = require("crypto"); // ✅ Import crypto module for hashing

let nfcReader = null;
let pendingStudent = { id: null }; // Pending student data

/**
 * Hashes the semester data and truncates it to 16 bytes.
 * @param {string} data - The semester data string to hash.
 * @returns {Buffer} - The hashed and truncated 16-byte buffer.
 */
function hashSemesterData(data) {
  const hash = crypto.createHash("sha256").update(data).digest(); // Create SHA-256 hash
  return hash.slice(0, 16); // ✅ Truncate to 16 bytes (128 bits)
}

function initializeNFC(io) {
  const nfc = new NFC();

  nfc.on("reader", (reader) => {
    console.log(`NFC Reader detected: ${reader.name}`);
    nfcReader = reader;

    reader.on("card", async (card) => {
      if (!pendingStudent.id) {
        console.log("No Student ID provided before scanning.");
        return;
      }
      console.log(
        `Card detected for Student ID ${pendingStudent.id}: ${card.uid}`
      );

      // Build a record with the student and card data.
      const record = { studentId: pendingStudent.id, uid: card.uid };

      // Emit event to Vue so it can store the registration in the database.
      io.emit("cardScanned", record);
      // Waiting for the "dbStored" event from Vue.
    });

    reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
    reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
  });

  nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));

  // Listen for the "dbStored" event from Vue.
  io.on("connection", (socket) => {
    socket.on("dbStored", async (data) => {
      console.log("Received dbStored event:", data);
      try {
        const block = 4; // Data storage block
        const sectorTrailerBlock = 7; // Sector Trailer (controls access to Block 4)
        const keyType = 0x60; // Key A authentication
        const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Default key
        const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]); // Custom key
        const semesterData = "2241901234"; // Example student data
        const hashedData = hashSemesterData(semesterData); // ✅ Hash and truncate to 16 bytes

        // Authenticate with Default Key A to write initial data
        await nfcReader.authenticate(block, keyType, defaultKey);

        // Write the hashed data to Block 4
        await nfcReader.write(block, hashedData, 16);
        console.log(`✅ Successfully wrote hashed data to Block ${block}`);

        // 🔐 Now Lock Block 4 by changing the Key A to a custom key
        await nfcReader.authenticate(sectorTrailerBlock, keyType, defaultKey);

        // Set new access bits: Readable by all, Write requires Custom Key A
        const accessBits = Buffer.from([0xff, 0x07, 0x80, 0x69]);

        const sectorTrailerNewKey = Buffer.concat([
          defaultKey, // New Key A (Custom Key)
          accessBits, // Access Bits
          defaultKey, // New Key B (same as Key A)
        ]);

        // Write the new security key to the sector trailer
        await nfcReader.write(sectorTrailerBlock, sectorTrailerNewKey, 16);
        console.log("🔒 Block 4 is now locked with a custom key!");

        // Emit success event to Vue
        io.emit("studentRegistered", data);
        pendingStudent.id = null;
      } catch (err) {
        console.error("❌ Error writing to NFC tag:", err.message);
        io.emit("registrationFailed", { message: err.message });
      }
    });
  });
}

module.exports = { initializeNFC, pendingStudent };
