// nfcHandler.js
const { NFC } = require("nfc-pcsc");

let nfcReader = null;
let pendingStudent = { id: null, semester: null };
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

    reader.on("card", async (card) => {
      if (!nfcListening) {
        console.log("⚠️ NFC scanning disabled.");
        return;
      }

      console.log(`✅ Card detected for Student ID ${pendingStudent.id}, Semester ${pendingStudent.semester}: ${card.uid}`);
      const record = { studentId: pendingStudent.id, uid: card.uid };
      io.emit("cardScanned", record);
      nfcListening = false;
    });
  });

  io.on("connection", (socket) => {
    socket.on("registerStudent", ({ studentID, semester }) => {
      pendingStudent.id = studentID;
      pendingStudent.semester = semester;
      nfcListening = true;
      socket.emit("nfcStatus", "Tap your NFC card now!");
    });

    socket.on("dbStored", async (data) => {
      try {
        const block = 4;
        const sectorTrailerBlock = 7;
        const keyType = 0x60;
        const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]);
        const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]);

        const semesterData = formatSemesterData(pendingStudent.semester, pendingStudent.id);
        console.log(`📌 Formatted semester data: ${semesterData}`);

        const authKey = await authenticateCard(nfcReader, block, defaultKey, customKey);
        await nfcReader.write(block, semesterData, 16);
        console.log(`✅ Successfully wrote semester data to Block ${block}`);

        if (authKey === "default") {
          await nfcReader.authenticate(sectorTrailerBlock, keyType, defaultKey);
          const accessBits = Buffer.from([0xff, 0x07, 0x80, 0x69]);
          const sectorTrailerNewKey = Buffer.concat([customKey, accessBits, customKey]);
          await nfcReader.write(sectorTrailerBlock, sectorTrailerNewKey, 16);
          console.log("🔒 Block 4 is now locked with a custom key!");
        }

        io.emit("studentRegistered", data);
        pendingStudent.id = null;
        pendingStudent.semester = null;
      } catch (err) {
        console.error("❌ Error writing to NFC:", err.message);
        io.emit("registrationFailed", { message: `NFC Write Error: ${err.message}` });
      }
    });
  });
}

module.exports = { initializeNFC };
