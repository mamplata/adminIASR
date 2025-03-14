const usb = require("usb");
const fs = require("fs");
const path = require("path");

const dataFilePath = path.join(__dirname, "../scannerRoles.json");

let lastConnected = new Set();

// Reads the scanner roles directly from the JSON file every time.
function getScannerRoles() {
    try {
        const data = fs.readFileSync(dataFilePath, "utf8");
        return JSON.parse(data);
    } catch (err) {
        return {};
    }
}

// Writes the provided roles object directly to the JSON file.
function saveScannerRoles(roles) {
    fs.writeFileSync(dataFilePath, JSON.stringify(roles, null, 2));
}

// Check if a device is an ACR122U scanner.
function isAcr122u(device) {
    return device.deviceDescriptor.idVendor === 0x072f && device.deviceDescriptor.idProduct === 0x2200;
}

// Checks whether a port is already assigned by always reading the latest roles.
function isPortAssigned(portPath) {
    const roles = getScannerRoles();
    return Object.keys(roles).some((key) => {
        const portIndex = key.lastIndexOf("-port");
        if (portIndex !== -1) {
            const assignedPort = key.substring(portIndex + 5);
            return assignedPort === portPath;
        }
        return false;
    });
}

// Extracts the port path from the unique key.
function getPortFromUniqueKey(uniqueKey) {
    const index = uniqueKey.lastIndexOf("-port");
    return index !== -1 ? uniqueKey.substring(index + 5) : "Unknown";
}

// Checks for connected scanners, emits events with the latest data from disk.
function checkScanners(socket, clientCookie) {
    const currentConnected = new Set();
    const roles = getScannerRoles();

    usb.getDeviceList().forEach((device) => {
        if (isAcr122u(device)) {
            const portPath = device.portNumbers ? device.portNumbers.join(".") : "Unknown";
            const uniqueKey = `${clientCookie}-port${portPath}`;
            currentConnected.add(uniqueKey);

            if (roles[uniqueKey]) {
                socket.emit("scannerDetected", {
                    uniqueKey,
                    portPath,
                    assigned: true,
                    role: roles[uniqueKey],
                });
            } else {
                socket.emit("scannerDetected", {
                    uniqueKey,
                    portPath,
                    assigned: false,
                });
            }
        }
    });

    // Emit disconnect events for scanners no longer present.
    lastConnected.forEach((deviceKey) => {
        if (!currentConnected.has(deviceKey)) {
            console.log(`🔴 Scanner Disconnected: ${deviceKey}`);
            socket.emit("scannerDisconnected", { uniqueKey: deviceKey });
        }
    });
    lastConnected = currentConnected;
}

module.exports = {
    checkScanners,
    isPortAssigned,
    getPortFromUniqueKey,
    saveScannerRoles,
    getScannerRoles,
};
