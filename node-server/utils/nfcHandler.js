const { NFC } = require("nfc-pcsc");

let nfcReader = null;
let pendingStudent = { id: null, semester: null };
let nfcListening = false;

function formatSemesterData(semester, studentId) {
  const semNumber = semester.charAt(0);
  const yearLastTwo = semester.slice(-2);
  let formattedData = `${semNumber}${yearLastTwo}${studentId}`;
  return Buffer.from(formattedData.padEnd(16, " "), "utf8");
}

async function authenticateCard(reader, block, defaultKey, customKey) {
  try {
    await reader.authenticate(block, 0x60, customKey);
    console.log("🔑 Successfully authenticated using Custom Key!");
    return "custom";
  } catch (err) {
    console.log("⚠️ Custom key authentication failed. Trying default key...");
  }

  try {
    await reader.authenticate(block, 0x60, defaultKey);
    console.log("🔑 Successfully authenticated using Default Key!");
    return "default";
  } catch (err) {
    console.error("❌ Failed to authenticate with both keys:", err.message);
    throw new Error("Authentication failed: Unable to access NFC block.");
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

      console.log(
        `✅ Card detected for Student ID ${pendingStudent.id}, Semester ${pendingStudent.semester}: ${card.uid}`
      );

      const record = { studentId: pendingStudent.id, uid: card.uid };
      io.emit("cardScanned", record);
      nfcListening = false;
    });

    reader.on("error", (err) => {
      console.error(`Reader error: ${err.message}`);
      io.emit("registrationFailed", { message: `Reader error: ${err.message}` });
    });

    reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
  });

  nfc.on("error", (err) => {
    console.error(`NFC error: ${err.message}`);
    io.emit("registrationFailed", { message: `NFC error: ${err.message}` });
  });

  io.on("connection", (socket) => {
    socket.on("registerStudent", ({ studentID, semester }) => {
      try {
        console.log(`Student Info received - ID: ${studentID}, Semester: ${semester}`);

        pendingStudent.id = studentID;
        pendingStudent.semester = semester;
        nfcListening = true;
        socket.emit("nfcStatus", "Tap your NFC card now!");
      } catch (err) {
        console.error("❌ Error in registerStudent:", err.message);
        socket.emit("registrationFailed", { message: err.message });
      }
    });

    socket.on("disconnect", () => {
      console.log(`User disconnected: ${socket.id}`);
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
        await nfcReader.write(block, Buffer.from(semesterData, "utf8"), 16);
        console.log(`✅ Successfully wrote semester data "${semesterData}" to Block ${block}`);

        if (authKey === "default") {
          console.log("🔒 Locking Block 4 with a custom key...");
          await nfcReader.authenticate(sectorTrailerBlock, keyType, defaultKey);

          const accessBits = Buffer.from([0xff, 0x07, 0x80, 0x69]);
          const sectorTrailerNewKey = Buffer.concat([customKey, accessBits, customKey]);

          await nfcReader.write(sectorTrailerBlock, sectorTrailerNewKey, 16);
          console.log("🔒 Block 4 is now locked with a custom key!");
        } else {
          console.log("🔄 Rewriting existing NFC card (custom key used).");
        }

        io.emit("studentRegistered", data);
        pendingStudent.id = null;
        pendingStudent.semester = null;
      } catch (err) {
        console.error("❌ Error writing to NFC tag:", err.message);
        io.emit("registrationFailed", { message: `NFC Write Error: ${err.message}` });
      }
    });

    socket.on("readCard", async () => {
      try {
        if (!nfcReader) {
          throw new Error("No NFC reader detected.");
        }

        console.log("📡 Waiting for NFC card...");

        nfcReader.once("card", async (card) => {
          console.log(`✅ Card detected: UID ${card.uid}`);

          const block = 4;
          const keyType = 0x60;
          const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]);

          try {
            await nfcReader.authenticate(block, keyType, customKey);
            console.log("🔑 Successfully authenticated using Custom Key!");

            const data = await nfcReader.read(block, 16);
            const formattedData = data.toString("utf8").trim();
            console.log(`📖 Read data: "${formattedData}"`);

            socket.emit("cardRead", { uid: card.uid, data: formattedData });
          } catch (err) {
            console.error("❌ Error reading NFC card:", err.message);
            socket.emit("registrationFailed", { message: `Read Error: ${err.message}` });
          }
        });
      } catch (err) {
        console.error("❌ Error during NFC read process:", err.message);
        socket.emit("registrationFailed", { message: `Read Process Error: ${err.message}` });
      }
    });
  });
}

module.exports = { initializeNFC };
