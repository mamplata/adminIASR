const usb = require('usb');
const readline = require('readline');

let lastConnected = new Set(); // Stores previously connected scanners
const scannerRoles = {}; // Stores scanner role assignments

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

// ✅ Function to check if a device is an ACR122U scanner
function isAcr122u(device) {
    return device.deviceDescriptor.idVendor === 0x072F && device.deviceDescriptor.idProduct === 0x2200;
}

// ✅ Function to prompt user for assigning roles
function assignScannerRole(uniqueKey, callback) {
    console.log(`🟢 Detected new scanner: ${uniqueKey}`);
    rl.question('Assign this scanner to (1) Time In or (2) Time Out? ', (answer) => {
        if (answer === '1') {
            scannerRoles[uniqueKey] = "Time In";
        } else if (answer === '2') {
            scannerRoles[uniqueKey] = "Time Out";
        } else {
            console.log("❌ Invalid choice. Skipping assignment.");
        }
        console.log(scannerRoles);
        callback();
    });
}

// ✅ Function to check and detect scanner changes
function checkScanners() {
    const currentConnected = new Set();

    usb.getDeviceList().forEach((device) => {
        if (isAcr122u(device)) {
            const portPath = device.portNumbers ? device.portNumbers.join('.') : 'Unknown';
            const uniqueKey = `bus${device.busNumber}-addr${device.deviceAddress}-port${portPath}`;

            currentConnected.add(uniqueKey);

            // New scanner detected
            if (!lastConnected.has(uniqueKey)) {
                if (scannerRoles[uniqueKey]) {
                    console.log(`🟢 Scanner ${uniqueKey} is already assigned as: ${scannerRoles[uniqueKey]}`);
                } else {
                    assignScannerRole(uniqueKey, () => { }); // Assign role if not yet assigned
                }
            }
        }
    });

    // ✅ Detect removed scanners
    lastConnected.forEach((device) => {
        if (!currentConnected.has(device)) {
            console.log(`🔴 Scanner Disconnected: ${device}`);
            delete scannerRoles[device]; // Remove from list
        }
    });

    lastConnected = currentConnected;
}

// ✅ Run the check every 2 seconds
console.log('🔄 Polling for scanner connections and removals...');
setInterval(checkScanners, 2000);
