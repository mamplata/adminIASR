const { NFC } = require("nfc-pcsc");
const usb = require("usb");
const {
  getAssignments,
  isNfcScanner,
  getPortFromUniqueKey,
} = require("./port");

// Global array to keep track of active NFC readers.
const activeNfcReaders = [];

// Global timer to debounce update calls.
let updateActiveNfcReadersTimer = null;

function updateActiveNfcReaders(deviceFingerprint) {
  // Clear any pending update call.
  if (updateActiveNfcReadersTimer) {
    clearTimeout(updateActiveNfcReadersTimer);
  }
  // Wait 500ms before performing the update.
  updateActiveNfcReadersTimer = setTimeout(() => {
    if (!deviceFingerprint) {
      console.log(
        "No device fingerprint provided; skipping updateActiveNfcReaders."
      );
      return;
    }
    const roles = getAssignments(deviceFingerprint);
    activeNfcReaders.forEach((reader) => {
      // Ensure the reader has the device fingerprint.
      if (!reader.deviceFingerprint) {
        reader.deviceFingerprint = deviceFingerprint;
      }
      const fp = reader.deviceFingerprint;
      // Use stored portNumbers on the reader if available.
      const portPath = reader.portNumbers
        ? reader.portNumbers.join(".")
        : getPortFromUniqueKey(reader.customId);
      const uniqueKey = `${fp}-port${portPath}`;
      console.log(
        `Checking reader ${reader.name} with uniqueKey: ${uniqueKey}`
      );
      if (roles[uniqueKey]) {
        reader.customId = uniqueKey;
        reader.role = roles[uniqueKey];
        console.log(
          `Updated reader ${reader.name} with customId: ${uniqueKey} and role: ${reader.role}`
        );
      }
    });
    updateActiveNfcReadersTimer = null;
  }, 500);
}

function initializeNFCRead(io) {
  const nfc = new NFC();

  nfc.on("reader", (reader) => {
    console.log(`📡 NFC Reader connected: ${reader.name}`);
    activeNfcReaders.push(reader);

    // Fingerprint should be set via client sync; if absent, leave it empty.
    const fp = reader.deviceFingerprint || "";
    const roles = getAssignments(fp);
    let matchedKey = null;
    let matchedPortPath = null;
    let matchedPortNumbers = null;

    // Use generic NFC scanner detection.
    const usbDevices = usb.getDeviceList().filter(isNfcScanner);
    for (const device of usbDevices) {
      // Determine the port path from the USB device.
      const portPath = device.portNumbers
        ? device.portNumbers.join(".")
        : "Unknown";
      for (const key in roles) {
        if (key.endsWith(`-port${portPath}`)) {
          matchedKey = key;
          matchedPortPath = portPath;
          matchedPortNumbers = device.portNumbers;
          break;
        }
      }
      if (matchedKey) break;
    }

    if (matchedKey) {
      reader.customId = matchedKey;
      reader.role = roles[matchedKey];
      // Store the port numbers on the reader.
      reader.portNumbers = matchedPortNumbers;
      console.log(
        `Assigned reader.customId: ${reader.customId} with role: ${reader.role}`
      );
    } else {
      // No assignment found; use first detected port if available.
      const defaultPort = usbDevices.length
        ? usbDevices[0].portNumbers
          ? usbDevices[0].portNumbers.join(".")
          : "Unknown"
        : "Unknown";
      reader.portNumbers = usbDevices.length ? usbDevices[0].portNumbers : null;
      // Only assign a customId if a fingerprint is present.
      reader.customId = fp ? `${fp}-port${defaultPort}` : "Unknown";
      console.log(
        `Reader ${reader.name} has no assigned role; assigned customId: ${reader.customId}`
      );
    }

    // Handle card detection.
    reader.on("card", async (card) => {
      console.log(`✅ Card detected on ${reader.customId}: UID ${card.uid}`);
      const block = 4;
      const keyType = 0x60;
      const customKey = Buffer.from([0xa0, 0xb1, 0xc2, 0xd3, 0xe4, 0xf5]);
      const defaultKey = Buffer.from([0xff, 0xff, 0xff, 0xff, 0xff, 0xff]);
      let usedKey = null;

      try {
        await reader.authenticate(block, keyType, customKey);
        usedKey = "Custom Key";
        console.log("🔑 Successfully authenticated using Custom Key!");
      } catch (err) {
        console.error("❌ Custom key authentication failed:", err.message);
        try {
          await reader.authenticate(block, keyType, defaultKey);
          usedKey = "Default Key";
          console.log("🔑 Successfully authenticated using Default Key!");
        } catch (errFallback) {
          console.error(
            "❌ Default key authentication also failed:",
            errFallback.message
          );
          const fp =
            reader.deviceFingerprint ||
            (reader.customId ? reader.customId.split("-port")[0] : "");
          return;
        }
      }

      try {
        const data = await reader.read(block, 16);
        const formattedData = data.toString("utf8").trim();
        console.log(`📖 Read data using ${usedKey}: "${formattedData}"`);
        const fp =
          reader.deviceFingerprint ||
          (reader.customId ? reader.customId.split("-port")[0] : "");
        // Emit a role-specific event based on the reader's role.
        if (reader.role === "Time In") {
          io.to(fp).emit("timeInCardRead", {
            uid: card.uid,
            reader: reader.customId,
            role: reader.role,
            data: formattedData,
          });
          console.log("Emitting timeInCardRead event");
        } else if (reader.role === "Time Out") {
          io.to(fp).emit("timeOutCardRead", {
            uid: card.uid,
            reader: reader.customId,
            role: reader.role,
            data: formattedData,
          });
          console.log("Emitting timeOutCardRead event");
        } else {
          io.to(fp).emit("readCard", {
            uid: card.uid,
            reader: reader.customId,
            role: reader.role,
            data: formattedData,
          });
        }
      } catch (readErr) {
        console.error("❌ Error reading NFC card:", readErr.message);
        const fp =
          reader.deviceFingerprint ||
          (reader.customId ? reader.customId.split("-port")[0] : "");
      }
    });

    reader.on("error", (err) => {
      console.error(`❌ Error on NFC Reader ${reader.name}: ${err.message}`);
      if (err.message.includes("SCardGetStatusChange")) {
        console.log(
          "Smart Card Resource Manager not running. Attempting to restart NFC reader..."
        );
        setTimeout(() => {
          console.log("Reinitializing NFC reader...");
          initializeNFCRead(io);
        }, 5000);
      }
    });

    reader.on("end", () => {
      console.log(`Reader ${reader.name} disconnected.`);
    });
  });

  nfc.on("error", (err) => {
    console.error(`NFC Read Error: ${err.message}`);
    if (err.message.includes("SCardGetStatusChange")) {
      console.log("Smart Card Resource Manager not running. Restarting NFC...");
      setTimeout(() => {
        initializeNFCRead(io);
      }, 5000);
    }
  });
}

module.exports = { initializeNFCRead, updateActiveNfcReaders };
