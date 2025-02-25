const { NFC } = require("nfc-pcsc");
const readline = require("readline-sync");

const nfc = new NFC();

// Define keys
const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Default Key A
const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]);

const blockToTest = 4; // Block to read/write
const sectorTrailerBlock = 7; // Controls access to Block 4

nfc.on("reader", (reader) => {
  console.log(`NFC Reader detected: ${reader.name}`);

  reader.on("card", async (card) => {
    console.log(`\nCard detected: ${card.uid}`);

    while (true) {
      console.log("\nChoose an option:");
      console.log("1. Read with Default Key");
      console.log("2. Read with Custom Key");
      console.log("3. Write with Default Key");
      console.log("4. Write with Custom Key");
      console.log("5. Set Custom Key (Lock Write for Default Key)");
      console.log("6. Revert to Default Key (Unlock Writing with Default Key)");
      console.log("7. Exit");

      const choice = readline.question("Enter your choice: ");

      try {
        switch (choice) {
          case "1": // Read with Default Key
            await reader.authenticate(blockToTest, 0x60, defaultKey);
            let data = await reader.read(blockToTest, 16, 16);
            console.log("✅ Read (Default Key):", data.toString("utf-8").trim());
            break;

          case "2": // Read with Custom Key
            await reader.authenticate(blockToTest, 0x60, customKey);
            let dataCustom = await reader.read(blockToTest, 16, 16);
            console.log("✅ Read (Custom Key):", dataCustom.toString("utf-8").trim());
            break;

          case "3": // Write with Default Key
            try {
              await reader.authenticate(blockToTest, 0x60, defaultKey);
              let writeData = Buffer.alloc(16, "DEF-WRITE-TEST");
              await reader.write(blockToTest, writeData, 16);
              console.log("✅ Write (Default Key) Successful");
            } catch (err) {
              console.log("❌ Write (Default Key) Failed:", err.message);
            }
            break;

          case "4": // Write with Custom Key
            await reader.authenticate(blockToTest, 0x60, customKey);
            let writeDataCustom = Buffer.alloc(16, "CUS-WRITE-TEST");
            await reader.write(blockToTest, writeDataCustom, 16);
            console.log("✅ Write (Custom Key) Successful");
            break;

          case "5": // Change to Custom Key
            await reader.authenticate(sectorTrailerBlock, 0x60, defaultKey);

            // Access Bits: Readable by all, Write requires Custom Key A
            const accessBitsCustom = Buffer.from([0xFF, 0x07, 0x80, 0x69]);

            const sectorTrailerCustomKey = Buffer.concat([
              customKey, // New Key A
              accessBitsCustom, // Access Bits
              customKey  // Key B (same as Key A for now)
            ]);

            await reader.write(sectorTrailerBlock, sectorTrailerCustomKey, 16);
            console.log("🔐 Block 4 now requires a Custom Key to write.");
            break;

          case "6": // Revert to Default Key (Unlock Writing for Default Key)
            await reader.authenticate(sectorTrailerBlock, 0x60, customKey);

            // Access Bits: Readable by all, Write requires Default Key A
            const accessBitsDefault = Buffer.from([0xFF, 0x07, 0x80, 0x69]);

            const sectorTrailerDefaultKey = Buffer.concat([
              defaultKey, // Restore Default Key A
              accessBitsDefault, // Access Bits
              defaultKey  // Restore Default Key B
            ]);

            await reader.write(sectorTrailerBlock, sectorTrailerDefaultKey, 16);
            console.log("🔓 Block 4 now allows writing with Default Key again.");
            break;

          case "7": // Exit
            console.log("Exiting NFC CRUD...");
            return;

          default:
            console.log("❌ Invalid choice. Try again.");
        }
      } catch (err) {
        console.log("❌ Error:", err.message);
      }
    }
  });

  reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
  reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
});

nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));
