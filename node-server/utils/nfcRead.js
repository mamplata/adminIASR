// nfcRead.js
const { NFC } = require("nfc-pcsc");

let nfcReader = null;

function initializeNFCRead(io) {
  const nfc = new NFC();

  nfc.on("reader", (reader) => {
    console.log(`📡 NFC Reader detected for reading: ${reader.name}`);
    nfcReader = reader;

    // Add an error handler on the reader to prevent server crashes
    reader.on("error", (err) => {
      console.error(`❌ Error on NFC Reader ${reader.name}: ${err.message}`);
      // Optionally, you can add logic here to attempt a reconnection or other fallback.
    });

    io.on("connection", (socket) => {
      socket.on("readCard", async () => {
        try {
          if (!nfcReader) throw new Error("No NFC reader detected.");
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
              socket.emit("readFailed", { message: `Read Error: ${err.message}` });
            }
          });
        } catch (err) {
          console.error("❌ Error during NFC read process:", err.message);
          socket.emit("readFailed", { message: `Read Process Error: ${err.message}` });
        }
      });
    });
  });

  nfc.on("error", (err) => console.error(`NFC Read Error: ${err.message}`));
}

module.exports = { initializeNFCRead };
