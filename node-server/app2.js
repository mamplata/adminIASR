const { NFC } = require("nfc-pcsc");
const readline = require("readline-sync");

function startNFC() {
    const nfc = new NFC();

    nfc.on("reader", (reader) => {
        console.log(`Reader detected: ${reader.name}`);

        reader.on("card", async (card) => {
            console.log(`Card detected: ${card.uid}`);

            const block = 4; // Data storage block
            const sectorTrailerBlock = 7; // Sector Trailer (controls access to block 4)
            const keyType = 0x60; // Key A (try Key B `0x61` if this fails)
            const key = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Default Key A

            try {

                // 3️⃣ Authenticate Block 4 before reading/writing
                await reader.authenticate(block, keyType, key);

                while (true) {
                    console.log("\nChoose an option:");
                    console.log("1. Create (Write Data)");
                    console.log("2. Read Data");
                    console.log("3. Update Data");
                    console.log("4. Delete Data");
                    console.log("5. Exit");
                    const choice = readline.question("Enter choice: ");

                    switch (choice) {
                        case "1": // Create (Write)
                            const textToWrite = readline.question("Enter text to write: ");
                            let data = Buffer.alloc(16, 0); // 16-byte block
                            data.write(textToWrite, 0, "utf-8");
                            await reader.write(block, data, 16);
                            console.log(`✅ Data written: "${textToWrite}"`);
                            break;

                        case "2": // Read
                            await reader.authenticate(block, keyType, key);
                            let readData = await reader.read(block, 16, 16);
                            readData = readData.toString("utf-8").replace(/\0/g, "").trim();
                            console.log(`📖 Read Data: "${readData}"`);
                            break;

                        case "3": // Update
                            await reader.authenticate(block, keyType, key);
                            let existingData = await reader.read(block, 16, 16);
                            existingData = existingData.toString("utf-8").replace(/\0/g, "").trim();
                            console.log(`Current Data: "${existingData}"`);
                            const newData = readline.question("Enter new data: ");
                            let updateBuffer = Buffer.alloc(16, 0);
                            updateBuffer.write(newData, 0, "utf-8");
                            await reader.write(block, updateBuffer, 16);
                            console.log(`🔄 Data updated to: "${newData}"`);
                            break;

                        case "4": // Delete (Clear)
                            await reader.authenticate(block, keyType, key);
                            let clearBuffer = Buffer.alloc(16, 0); // Empty buffer
                            await reader.write(block, clearBuffer, 16);
                            console.log("🗑️ Data deleted from NFC tag.");
                            break;

                        case "5": // Exit
                            console.log("🚪 Exiting...");
                            return;

                        default:
                            console.log("❌ Invalid choice. Try again.");
                    }
                }
            } catch (err) {
                console.error("❌ Error:", err);
            }
        });

        reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
        reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
    });

    nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));
}

// Start the NFC service without authentication
startNFC();
