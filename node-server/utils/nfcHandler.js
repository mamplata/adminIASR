const { NFC } = require("nfc-pcsc");

let nfcReader = null;
let pendingStudent = { id: null }; // ✅ Change from `null` to an object
const registeredStudents = [];

/**
 * initializeNFC()
 * Sets up the NFC reader and handles scanning.
 */
function initializeNFC(io) {
  const nfc = new NFC();

  nfc.on("reader", (reader) => {
    console.log(`NFC Reader detected: ${reader.name}`);
    nfcReader = reader;

    reader.on("card", async (card) => {
      if (!pendingStudent.id) {  // ✅ Use `pendingStudent.id` instead of `pendingStudentID`
        console.log("No Student ID provided before scanning.");
        return;
      }

      console.log(`Card detected for Student ID ${pendingStudent.id}: ${card.uid}`);

      const block = 4; // Data storage block
      const sectorTrailerBlock = 7; // Sector Trailer (controls access to Block 4)
      const keyType = 0x60; // Key A
      const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Default Key A
      const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]); // Custom Key A
      const semesterData = "2ndsem_2024-2025"; // Data to be written

      console.log(pendingStudent); // ✅ Now shows the correct Student ID

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
        const accessBits = Buffer.from([0xFF, 0x07, 0x80, 0x69]);

        const sectorTrailerNewKey = Buffer.concat([
          customKey,  // New Key A (Custom Key)
          accessBits, // Access Bits
          customKey   // New Key B (same as Key A)
        ]);
        console.log("🔒 dsadasds!");

        // Write new keys to the sector trailer
        await reader.write(sectorTrailerBlock, sectorTrailerNewKey, 16);
        console.log("🔒 Block 4 is now locked with a custom key!");

        // Save the Student ID and NFC UID
        const studentRecord = { studentID: pendingStudent.id, nfcUID: card.uid };
        registeredStudents.push(studentRecord);

        // Emit updated student list to all clients
        io.emit("studentRegistered", registeredStudents);

        // Reset pending Student ID
        pendingStudent.id = null;
      } catch (err) {
        console.error("❌ Error writing to NFC tag:", err.message);
      }
    });

    reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
    reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
  });

  nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));
}

module.exports = { initializeNFC, pendingStudent }; // ✅ Export as an object
