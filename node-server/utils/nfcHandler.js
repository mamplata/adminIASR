const { NFC } = require("nfc-pcsc");

let nfcReader = null;
let pendingStudent = { id: null }; // pending student data

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
      console.log(`Card detected for Student ID ${pendingStudent.id}: ${card.uid}`);

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
        const block = 4;
        const keyType = 0x60;
        const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]);
        const semesterData = "2241901234";

        // Authenticate and write data to the NFC card.
        await nfcReader.authenticate(block, keyType, defaultKey);
        let dataBuffer = Buffer.alloc(16, 0);
        dataBuffer.write(semesterData, 0, "utf-8");
        await nfcReader.write(block, dataBuffer, 16);
        console.log(`✅ Successfully wrote "${semesterData}" to Block ${block}`);

        // Emit a success event to update the UI on the Vue side.
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
