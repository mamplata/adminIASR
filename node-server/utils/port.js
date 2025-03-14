const usb = require('usb');
const fs = require('fs');
const path = require('path');

const dataFilePath = path.join(__dirname, '../scannerRoles.json');

let lastConnected = new Set();
let scannerRoles = {};

// Load stored assignments once at startup.
function loadScannerRoles() {
    try {
        const data = fs.readFileSync(dataFilePath, 'utf8');
        scannerRoles = JSON.parse(data);
        console.log('📂 Loaded scanner roles:ss', scannerRoles);
    } catch (err) {
        console.log('⚠️ No existing scanner roles found, starting fresh.');
        scannerRoles = {};
    }
}
loadScannerRoles();

// Save assignments to disk.
function saveScannerRoles() {
    fs.writeFileSync(dataFilePath, JSON.stringify(scannerRoles, null, 2));
}

// Check if a device is an ACR122U scanner.
function isAcr122u(device) {
    return device.deviceDescriptor.idVendor === 0x072F && device.deviceDescriptor.idProduct === 0x2200;
}

function isPortAssigned(portPath) {
    return Object.keys(scannerRoles).some(key => {
        const portIndex = key.lastIndexOf('-port');
        if (portIndex !== -1) {
            const assignedPort = key.substring(portIndex + 5);
            return assignedPort === portPath;
        }
        return false;
    });
}

function getPortFromUniqueKey(uniqueKey) {
    const index = uniqueKey.lastIndexOf('-port');
    return index !== -1 ? uniqueKey.substring(index + 5) : 'Unknown';
}

function checkScanners(socket, clientCookie) {
    // Do not reload here—use the in‑memory data.
    const currentConnected = new Set();

    usb.getDeviceList().forEach((device) => {
        if (isAcr122u(device)) {
            const portPath = device.portNumbers ? device.portNumbers.join('.') : 'Unknown';
            const uniqueKey = `${clientCookie}-port${portPath}`;
            currentConnected.add(uniqueKey);

            if (scannerRoles[uniqueKey]) {
                socket.emit('scannerDetected', {
                    uniqueKey,
                    portPath,
                    assigned: true,
                    role: scannerRoles[uniqueKey],
                });
            } else {
                socket.emit('scannerDetected', {
                    uniqueKey,
                    portPath,
                    assigned: false,
                });
            }
        }
    });

    lastConnected.forEach((deviceKey) => {
        if (!currentConnected.has(deviceKey)) {
            console.log(`🔴 Scanner Disconnected: ${deviceKey}`);
            socket.emit('scannerDisconnected', { uniqueKey: deviceKey });
        }
    });
    lastConnected = currentConnected;
}

module.exports = {
    checkScanners,
    isPortAssigned,
    getPortFromUniqueKey,
    saveScannerRoles,
    loadScannerRoles,
    scannerRoles,
};
