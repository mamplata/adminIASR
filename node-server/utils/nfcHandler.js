// nfcHandler.js
const { NFC } = require("nfc-pcsc");

let nfcReader = null;
let nfcListening = false;

function formatSemesterData(semester, studentId) {
  const semNumber = semester.charAt(0);
  const yearLastTwo = semester.slice(-2);
  return Buffer.from(`${semNumber}${yearLastTwo}${studentId}`.padEnd(16, " "), "utf8");
}

async function authenticateCard(reader, block, defaultKey, customKey) {
  try {
    await reader.authenticate(block, 0x60, customKey);
    return "custom";
  } catch {
    console.log("⚠️ Custom key failed. Trying default...");
  }

  try {
    await reader.authenticate(block, 0x60, defaultKey);
    return "default";
  } catch (err) {
    throw new Error("Authentication failed");
  }
}

function initializeNFC(io) {
  const nfc = new NFC();

  nfc.on("reader", (reader) => {
    console.log(`NFC Reader detected: ${reader.name}`);
    nfcReader = reader;

    // Add an error listener to prevent the process from crashing
    reader.on("error", (err) => {
      console.error(`❌ Error on reader ${reader.name}:`, err);
    });

    reader.on("card", async (card) => {
      // Only process card events if scanning is enabled.
      if (!nfcListening) {
        console.log("⚠️ NFC scanning disabled.");
        return;
      }
      console.log(`✅ Card detected with UID: ${card.uid}`);
      // Emit the UID; the client will later include it with student data.
      io.emit("cardScanned", { uid: card.uid });
      nfcListening = false;
    });
  });

  io.on("connection", (socket) => {
    // Start scanning when the client is ready.
    socket.on("startScan", () => {
      nfcListening = true;
      socket.emit("nfcStatus", "Tap your NFC card now!");
    });

    // Stop scanning when the client cancels.
    socket.on("cancelScan", () => {
      nfcListening = false;
      console.log("NFC scanning cancelled by the client.");
    });

    // Handle registration confirmation, including the scanned UID.
    // Expected data: { studentID, semester, uid }
    socket.on("confirmRegistration", async (data) => {
      try {
        const { studentID, semester, uid } = data;
        const block = 4;
        const sectorTrailerBlock = 7;
        const keyType = 0x60;
        const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]);
        const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]);

        const semesterData = formatSemesterData(semester, studentID);
        console.log(`📌 Formatted semester data: ${semesterData}`);

        // Attempt authentication and write to the card.
        const authKey = await authenticateCard(nfcReader, block, defaultKey, customKey);
        await nfcReader.write(block, semesterData, 16);
        console.log(`✅ Successfully wrote semester data to Block ${block}`);

        // If the card was authenticated using the default key, update the sector trailer.
        if (authKey === "default") {
          await nfcReader.authenticate(sectorTrailerBlock, keyType, defaultKey);
          const accessBits = Buffer.from([0xff, 0x07, 0x80, 0x69]);
          const sectorTrailerNewKey = Buffer.concat([customKey, accessBits, customKey]);
          await nfcReader.write(sectorTrailerBlock, sectorTrailerNewKey, 16);
          console.log("🔒 Block 4 is now locked with a custom key!");
        }

        socket.emit("studentRegistered", data);
      } catch (err) {
        console.error("❌ Error writing to NFC:", err.message);
        socket.emit("registrationFailed", { message: `NFC Write Error: ${err.message}` });
      }
    });
  });
}

module.exports = { initializeNFC };
