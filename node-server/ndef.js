const { NFC } = require("nfc-pcsc");
const ndef = require("ndef");

const nfc = new NFC();
const wakdevKey = Buffer.from([0xd3, 0xf7, 0xd3, 0xf7, 0xd3, 0xf7]); // Wakdev Custom Key
const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Default Key
const blockToRead = 4; // NDEF usually starts at Block 4

nfc.on("reader", (reader) => {
    console.log(`🛠️ NFC Reader detected: ${reader.name}`);

    reader.on("card", async (card) => {
        console.log(`💳 Card detected: ${card.uid}`);

        let authenticated = false;

        // 🔑 Try Wakdev Custom Key first
        try {
            await reader.authenticate(blockToRead, 0x60, wakdevKey);
            console.log("✅ Authentication successful with Wakdev Key!");
            authenticated = true;
        } catch {
            console.log("❌ Wakdev Key failed, trying Default Key...");

            // 🔑 Try Default Key if Wakdev Key fails
            try {
                await reader.authenticate(blockToRead, 0x60, defaultKey);
                console.log("✅ Authentication successful with Default Key!");
                authenticated = true;
            } catch {
                console.log("❌ Default Key also failed. Cannot read.");
            }
        }

        if (!authenticated) return;

        try {
            // 📖 Read NDEF data from NFC tag
            let rawData = await reader.read(blockToRead, 48, 16);
            
            // Decode NDEF message
            let ndefMessage = ndef.decodeMessage(rawData);
            
            // 🔍 Display NDEF Records
            console.log("📡 NDEF Data:");
            ndefMessage.forEach((record, index) => {
                let decodedData;
                
                // Check record type
                if (record.type.toString("utf-8") === "T") { // Text Record
                    decodedData = ndef.text.decodePayload(record.payload);
                } else if (record.type.toString("utf-8") === "U") { // URI Record
                    decodedData = ndef.uri.decodePayload(record.payload);
                } else {
                    decodedData = record.payload.toString("utf-8");
                }

                console.log(`📝 Record ${index + 1}: Type: ${record.type.toString("utf-8")}, Data: "${decodedData}"`);
            });
        } catch (err) {
            console.error("❌ Read Error:", err);
        }
    });

    reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
    reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
});

nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));
 