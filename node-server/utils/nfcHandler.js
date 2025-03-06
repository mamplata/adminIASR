const { NFC } = require("nfc-pcsc");

let nfcReader = null;
let pendingStudent = { id: null, semester: null }; // Store both Student ID and Semester
let nfcListening = false; // ✅ Flag to control NFC scanning

/**
 * Formats the semester data correctly as `2241901234`.
 * @param {string} semester - The semester string (e.g., "2nd2024").
 * @param {string} studentId - The student ID (e.g., "1901234").
 * @returns {string} - The formatted semester data.
 */
function formatSemesterData(semester, studentId) {
  const semNumber = semester.charAt(0); // Get first digit (e.g., "2" from "2nd")
  const yearLastTwo = semester.slice(-2); // Get last two digits of the year (e.g., "24" from "2024")
  let formattedData = `${semNumber}${yearLastTwo}${studentId}`; // Combine for final format

  // Ensure exactly 16 bytes (pad with spaces if too short)
  return Buffer.from(formattedData.padEnd(16, " "), "utf8");
}

/**
 * Attempts to authenticate the NFC card using a custom key first, then falls back to the default key.
 * @param {object} reader - The NFC reader instance.
 * @param {number} block - The block to authenticate.
 * @param {Buffer} defaultKey - The default authentication key.
 * @param {Buffer} customKey - The custom authentication key.
 * @returns {string} - The authentication status ("custom" or "default").
 */
async function authenticateCard(reader, block, defaultKey, customKey) {
  try {
    // ✅ First, try authenticating with the custom key
    await reader.authenticate(block, 0x60, customKey);
    console.log("🔑 Successfully authenticated using Custom Key!");
    return "custom";
  } catch (err) {
    console.log("⚠️ Custom key authentication failed. Trying default key...");
  }

  try {
    // ✅ If custom key fails, try authenticating with the default key
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
        console.log(
          "⚠️ NFC scanning disabled. Waiting for student registration..."
        );
        return;
      }

      console.log(
        `✅ Card detected for Student ID ${pendingStudent.id}, Semester ${pendingStudent.semester}: ${card.uid}`
      );

      // Build a record with the student and card data.
      const record = {
        studentId: pendingStudent.id,
        uid: card.uid,
      };

      // Emit event to Vue so it can store the registration in the database.
      io.emit("cardScanned", record);

      // Disable NFC listening after a successful scan
      nfcListening = false;
    });

    reader.on("error", (err) => console.error(`Reader error: ${err.message}`));
    reader.on("end", () => console.log(`Reader ${reader.name} removed.`));
  });

  nfc.on("error", (err) => console.error(`NFC error: ${err.message}`));

  io.on("connection", (socket) => {
    socket.on("registerStudent", ({ studentID, semester }) => {
      console.log(
        `Student Info received - ID: ${studentID}, Semester: ${semester}`
      );

      // Store student ID and semester in pendingStudent
      pendingStudent.id = studentID;
      pendingStudent.semester = semester;

      // ✅ Enable NFC card reading only after student registration
      nfcListening = true;
      socket.emit("nfcStatus", "Tap your NFC card now!");
    });

    socket.on("disconnect", () => {
      console.log(`User disconnected: ${socket.id}`);
    });

    socket.on("dbStored", async (data) => {
      console.log("Received dbStored event:", data);
      try {
        const block = 4; // Data storage block
        const sectorTrailerBlock = 7; // Sector Trailer (controls access to Block 4)
        const keyType = 0x60; // Key A authentication
        const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]); // Default key
        const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]); // Custom key

        // ✅ Format the semester data correctly
        const semesterData = formatSemesterData(
          pendingStudent.semester,
          pendingStudent.id
        );
        console.log(`📌 Formatted semester data: ${semesterData}`);

        // ✅ Determine which key to use for authentication
        const authKey = await authenticateCard(
          nfcReader,
          block,
          defaultKey,
          customKey
        );

        // ✅ Write formatted semester data to Block 4
        await nfcReader.write(block, Buffer.from(semesterData, "utf8"), 16);
        console.log(
          `✅ Successfully wrote semester data "${semesterData}" to Block ${block}`
        );

        // ✅ If it's a new card (default key used), lock the card with a custom key
        if (authKey === "default") {
          console.log("🔒 Locking Block 4 with a custom key...");

          // Authenticate Sector Trailer Block using Default Key
          await nfcReader.authenticate(sectorTrailerBlock, keyType, defaultKey);

          // Set new access bits: Readable by all, Write requires Custom Key A
          const accessBits = Buffer.from([0xff, 0x07, 0x80, 0x69]);

          const sectorTrailerNewKey = Buffer.concat([
            customKey, // New Key A (Custom Key)
            accessBits, // Access Bits
            customKey, // New Key B (same as Key A)
          ]);

          // Write the new security key to the sector trailer
          await nfcReader.write(sectorTrailerBlock, sectorTrailerNewKey, 16);
          console.log("🔒 Block 4 is now locked with a custom key!");
        } else {
          console.log("🔄 Rewriting existing NFC card (custom key used).");
        }

        // Emit success event to Vue
        io.emit("studentRegistered", data);

        // Reset pendingStudent after storing
        pendingStudent.id = null;
        pendingStudent.semester = null;
      } catch (err) {
        console.error("❌ Error writing to NFC tag:", err.message);
        io.emit("registrationFailed", { message: err.message });
      }
    });

    io.on("connection", (socket) => {
      socket.on("readCard", async () => {
        try {
          if (!nfcReader) {
            throw new Error("No NFC reader detected.");
          }

          console.log("📡 Waiting for NFC card...");

          // Attach event listener for card detection
          nfcReader.once("card", async (card) => {
            console.log(`✅ Card detected: UID ${card.uid}`);

            const block = 4; // Data storage block
            const keyType = 0x60; // Key A authentication
            const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]); // Custom key

            try {
              // ✅ Authenticate with the custom key
              await nfcReader.authenticate(block, keyType, customKey);
              console.log("🔑 Successfully authenticated using Custom Key!");

              // ✅ Read the 16-byte block
              const data = await nfcReader.read(block, 16);
              const formattedData = data.toString("utf8").trim(); // Convert buffer to string
              console.log(`📖 Read data: "${formattedData}"`);

              // ✅ Emit the UID and stored data to the client
              socket.emit("cardRead", { uid: card.uid, data: formattedData });
            } catch (err) {
              console.error("❌ Error reading NFC card:", err.message);
              socket.emit("readFailed", { message: err.message });
            }
          });
        } catch (err) {
          console.error("❌ Error during NFC read process:", err.message);
          socket.emit("readFailed", { message: err.message });
        }
      });
    });
  });
}

module.exports = { initializeNFC };
