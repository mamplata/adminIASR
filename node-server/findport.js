const usb = require('usb');
const fs = require('fs');
const path = require('path');
const readline = require('readline');
const { NFC } = require("nfc-pcsc");

const EventEmitter = require('events');
const roleEmitter = new EventEmitter();

// Define the path to the JSON file for persistence (stored on the LAN server)
const dataFilePath = path.join(__dirname, 'scannerRoles.json');

// Hardcoded client cookie value (in practice, retrieve this from the client's request)
const clientCookie = "9576bdbe-7d6f-4a6e-83c2-815cd012b8af";

let scannerRoles = {};         // Persistent role assignments: key = `${clientCookie}-port${port}`
let activeNfcReaders = [];     // Array to track active NFC reader objects
let pendingPrompts = new Set(); // Track scanners that are currently being prompted

// A helper that only logs if no prompt is active.
function safeLog(...args) {
    if (pendingPrompts.size === 0) {
        console.log(...args);
    }
}

// Load stored assignments from the JSON file at startup.
function loadScannerRoles() {
    try {
        const data = fs.readFileSync(dataFilePath, 'utf8');
        scannerRoles = JSON.parse(data);
        safeLog('📂 Loaded scanner roles:', scannerRoles);
    } catch (err) {
        safeLog('⚠️ No existing scanner roles found, starting fresh.');
        scannerRoles = {};
    }
}

// Save assignments to the JSON file for persistence.
function saveScannerRoles() {
    fs.writeFileSync(dataFilePath, JSON.stringify(scannerRoles, null, 2));
}

loadScannerRoles();

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

// Check if a device is an ACS ACR122U scanner.
function isAcr122u(device) {
    return device.deviceDescriptor.idVendor === 0x072F && device.deviceDescriptor.idProduct === 0x2200;
}

// Get a port identifier string for a USB device.
function getPortPath(device) {
    return device.portNumbers ? device.portNumbers.join('.') : 'Unknown';
}

// Update active NFC readers: For each active reader, try to match a connected USB device
// that has a saved role. If a match is found, update the reader's customId and role.
function updateActiveNfcReaders() {
    const usbDevices = usb.getDeviceList().filter(isAcr122u);
    activeNfcReaders.forEach(reader => {
        let matchedKey = null;
        for (const device of usbDevices) {
            const portPath = getPortPath(device);
            const uniqueKey = `${clientCookie}-port${portPath}`;
            if (scannerRoles[uniqueKey]) {
                matchedKey = uniqueKey;
                break;
            }
        }
        if (matchedKey) {
            reader.customId = matchedKey;
            reader.role = scannerRoles[matchedKey];
            safeLog(`(Update) Reader ${reader.name} now assigned unique ID: ${reader.customId} with role: ${reader.role}`);
        } else {
            safeLog(`(Update) Reader ${reader.name} remains unassigned (Unknown).`);
        }
        safeLog(reader);
    });
}

// Prompt user for assigning a role for a scanner.
// If the selected role is already used for this client, ask whether to override the existing assignment.
function promptAssignRole(uniqueKey, portPath, callback) {
    console.log(`🟢 Detected scanner without role on port ${portPath} (Key: ${uniqueKey})`);
    rl.question('Assign this scanner to (1) Time In or (2) Time Out? ', (answer) => {
        let selectedRole = null;
        if (answer === '1') {
            selectedRole = "Time In";
        } else if (answer === '2') {
            selectedRole = "Time Out";
        } else {
            console.log("❌ Invalid choice. Skipping assignment.");
            return callback();
        }

        // Only consider assignments for the current client cookie.
        const rolesAlreadyAssigned = Object.keys(scannerRoles)
            .filter(key => key.startsWith(clientCookie))
            .map(key => scannerRoles[key]);

        if (rolesAlreadyAssigned.includes(selectedRole)) {
            // Find the existing key for the role.
            const existingKeyForRole = Object.keys(scannerRoles)
                .find(key => key.startsWith(clientCookie) && scannerRoles[key] === selectedRole);
            if (existingKeyForRole && existingKeyForRole !== uniqueKey) {
                // Role already assigned to another port; ask if the user wants to override.
                rl.question(`Role ${selectedRole} is already assigned to ${existingKeyForRole}. Override? (y/N): `, (overrideAnswer) => {
                    if (overrideAnswer.toLowerCase() === 'y') {
                        delete scannerRoles[existingKeyForRole];
                        scannerRoles[uniqueKey] = selectedRole;
                        console.log(`✅ Overridden assignment. Scanner on port ${portPath} is now assigned as ${selectedRole}.`);
                    } else {
                        console.log(`❌ Keeping existing role ${selectedRole}.`);
                    }
                    console.log('Updated roles:', scannerRoles);
                    saveScannerRoles();
                    updateActiveNfcReaders();
                    callback();
                });
            } else {
                // This port already has the role; no override needed.
                console.log(`✅ Scanner on port ${portPath} already has the role ${selectedRole}.`);
                callback();
            }
        } else {
            // No existing assignment for the selected role for this client, assign directly.
            scannerRoles[uniqueKey] = selectedRole;
            console.log(`✅ Scanner on port ${portPath} is assigned as: ${selectedRole}`);
            console.log('Updated roles:', scannerRoles);
            saveScannerRoles();
            updateActiveNfcReaders();
            callback();
        }
    });
}

// Check for changes in scanner connections and re-check assignments.
function checkScanners() {
    // Reload roles from the file to capture any manual changes.
    loadScannerRoles();
    const usbDevices = usb.getDeviceList().filter(isAcr122u);
    usbDevices.forEach((device) => {
        const portPath = getPortPath(device);
        // Build the unique key using only the client cookie and port.
        const uniqueKey = `${clientCookie}-port${portPath}`;

        // If a scanner does not have an assigned role and isn't already being prompted, prompt for assignment.
        if (!scannerRoles[uniqueKey] && !pendingPrompts.has(uniqueKey)) {
            pendingPrompts.add(uniqueKey);
            promptAssignRole(uniqueKey, portPath, () => {
                pendingPrompts.delete(uniqueKey);
            });
        } else if (scannerRoles[uniqueKey]) {
            safeLog(`✅ Scanner on port ${portPath} is assigned as: ${scannerRoles[uniqueKey]}`);
        }
    });
}

safeLog('🔄 Polling for scanner connections and removals...');
setInterval(checkScanners, 2000);

// NFC integration: Listen for NFC reader events.
const nfc = new NFC();

nfc.on("reader", (reader) => {
    // Add reader to active list.
    activeNfcReaders.push(reader);
    console.log(`📡 NFC Reader connected: ${reader.name}`);
    
    // Try to match the NFC reader with a USB device that has an assigned role.
    let matchedKey = null;
    const usbDevices = usb.getDeviceList().filter(isAcr122u);
    for (const device of usbDevices) {
        const portPath = getPortPath(device);
        const uniqueKey = `${clientCookie}-port${portPath}`;
        if (scannerRoles[uniqueKey]) {
            matchedKey = uniqueKey;
            break;
        }
    }

    if (matchedKey) {
        reader.customId = matchedKey;
        reader.role = scannerRoles[matchedKey];
        console.log(`Reader detected: ${reader.name} assigned unique ID: ${reader.customId} with role: ${reader.role}`);
    } else {
        reader.customId = `${clientCookie}-portUnknown`;
        console.log(`Reader detected: ${reader.name} assigned unique ID: ${reader.customId} (role not yet assigned)`);
    }

    console.log(reader);

    reader.on("card", (card) => {
        console.log(`Card detected on ${reader.customId} (${reader.role || 'no role assigned'}): UID ${card.uid}`);
    });

    reader.on("error", (err) => {
        console.error(`Error on reader ${reader.name}: ${err.message}`);
    });

    reader.on("end", () => {
        console.log(`Reader ${reader.name} disconnected.`);
        // Remove from active readers.
        activeNfcReaders = activeNfcReaders.filter(r => r !== reader);
    });
});
