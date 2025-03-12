// ./utils/port.js
const usb = require('usb');
const fs = require('fs');
const path = require('path');
const readline = require('readline');

const dataFilePath = path.join(__dirname, '../scannerRoles.json');

let lastConnected = new Set(); // Stores previously connected scanners
let scannerRoles = {};         // Stores scanner role assignments

// Load stored assignments from the JSON file at startup
function loadScannerRoles() {
    try {
        const data = fs.readFileSync(dataFilePath, 'utf8');
        scannerRoles = JSON.parse(data);
        console.log('📂 Loaded scanner roles:', scannerRoles);
    } catch (err) {
        console.log('⚠️ No existing scanner roles found, starting fresh.');
        scannerRoles = {};
    }
}

// Save assignments to the JSON file for persistence
function saveScannerRoles() {
    fs.writeFileSync(dataFilePath, JSON.stringify(scannerRoles, null, 2));
}

loadScannerRoles();

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

// Check if a device is an ACR122U scanner
function isAcr122u(device) {
    return device.deviceDescriptor.idVendor === 0x072F && device.deviceDescriptor.idProduct === 0x2200;
}

// Check if a port is already assigned by comparing port paths
function isPortAssigned(portPath) {
    return Object.keys(scannerRoles).some(key => {
        const portIndex = key.lastIndexOf('-port');
        if (portIndex !== -1) {
            const assignedPort = key.substring(portIndex + 5); // extract after '-port'
            return assignedPort === portPath;
        }
        return false;
    });
}

// Prompt user for assigning a role to a scanner
function assignScannerRole(uniqueKey, portPath, callback) {
    // If this exact device is already assigned, skip prompting.
    if (scannerRoles[uniqueKey]) {
        console.log(`✅ Scanner ${uniqueKey} is already assigned as: ${scannerRoles[uniqueKey]}`);
        return callback();
    }
    // If any scanner using the same port is already assigned, skip prompting.
    if (isPortAssigned(portPath)) {
        console.log(`⚠️ Port ${portPath} is already assigned. Skipping scanner ${uniqueKey}.`);
        return callback();
    }
    console.log(`🟢 Detected new scanner: ${uniqueKey} on port ${portPath}`);
    rl.question('Assign this scanner to (1) Time In or (2) Time Out? ', (answer) => {
        if (answer === '1') {
            scannerRoles[uniqueKey] = "Time In";
        } else if (answer === '2') {
            scannerRoles[uniqueKey] = "Time Out";
        } else {
            console.log("❌ Invalid choice. Skipping assignment.");
        }
        console.log(scannerRoles);
        saveScannerRoles(); // Persist the new assignment
        callback();
    });
}

// Function to detect and check scanner connections.
// The clientCookie should come from a trusted source (for example, via an environment variable or webhook).
function checkScanners(clientCookie) {
    const currentConnected = new Set();

    usb.getDeviceList().forEach((device) => {
        if (isAcr122u(device)) {
            const portPath = device.portNumbers ? device.portNumbers.join('.') : 'Unknown';
            // Build a unique key using the provided client cookie
            const uniqueKey = `${clientCookie}-bus${device.busNumber}-addr${device.deviceAddress}-port${portPath}`;
            currentConnected.add(uniqueKey);

            // New scanner detected
            if (!lastConnected.has(uniqueKey)) {
                assignScannerRole(uniqueKey, portPath, () => { });
            }
        }
    });

    // Detect removed scanners and update assignments
    lastConnected.forEach((deviceKey) => {
        if (!currentConnected.has(deviceKey)) {
            console.log(`🔴 Scanner Disconnected: ${deviceKey}`);
            delete scannerRoles[deviceKey];
            saveScannerRoles();
        }
    });

    lastConnected = currentConnected;
}

module.exports = {
    checkScanners,
};
