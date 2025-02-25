const { NFC } = require("nfc-pcsc");

const nfc = new NFC();
const sectorTrailerBlock = 7; // Controls authentication for blocks 4-6
const keyType = 0x60; // Key A
const currentKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Try the default first (replace if needed)
const newKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]); // New Key A

nfc.on("reader", (reader) => {
    console.log(`🛠️ NFC Reader detected: ${reader.name}`);

    reader.on("card", async (card) => {
        console.log(`💳 Card detected: ${card.uid}`);

        try {
  
            console.log("✅ Authentication successful!");

            // 2️⃣ Set the new Key A and access bits
            const accessBits = Buffer.from([0x7F, 0x07, 0x88, 0x40]); // Read allowed, Write requires Key A
            const newSectorTrailer = Buffer.concat([
                newKey,     // New Key A
                accessBits, // Access Bits
                newKey      // Key B (same as A)
            ]);

            // 3️⃣ Write the new key to the sector trailer
            await reader.write(sectorTrailerBlock, newSectorTrailer, 16);
            console.log("🔐 New authentication key set successfully!");

            // 4️⃣ Verify by authenticating with the new key
            await reader.authenticate(sectorTrailerBlock, keyType, newKey);
            console.log("✅ Re-authenticated with new key!");

        } catch (err) {
            console.error("❌ Error:", err);
        }
    });

    reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
    reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
});

nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));
