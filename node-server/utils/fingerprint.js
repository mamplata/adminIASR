//fingerprint.js
const { machineIdSync } = require("node-machine-id");
const macaddress = require("macaddress");
const si = require("systeminformation");
const crypto = require("crypto");

/**
 * generateFingerprint()
 * Generates a unique device fingerprint.
 */
async function generateFingerprint() {
  try {
    const machineId = machineIdSync({ original: true });
    const mac = await macaddress.one();
    const uuidData = await si.uuid();
    const hardwareUUID = uuidData.hardware || '';

    const combined = machineId + hardwareUUID + mac;
    return crypto.createHash('sha256').update(combined).digest('hex');
  } catch (err) {
    console.error("❌ Error generating fingerprint:", err);
    return null;
  }
}

module.exports = { generateFingerprint };
