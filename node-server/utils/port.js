const usb = require("usb");

// In-memory assignments: keys are deviceFingerprint, values are assignment objects.
const assignments = {};

function getAssignments(deviceFingerprint) {
  return assignments[deviceFingerprint] || {};
}

function setAssignment(deviceFingerprint, uniqueKey, role) {
  if (!assignments[deviceFingerprint]) {
    assignments[deviceFingerprint] = {};
  }
  assignments[deviceFingerprint][uniqueKey] = role;
}

function removeAssignment(deviceFingerprint, uniqueKey) {
  if (assignments[deviceFingerprint]) {
    delete assignments[deviceFingerprint][uniqueKey];
  }
}

function isNfcScanner(device) {
  if (device.configDescriptor && device.configDescriptor.interfaces) {
    for (const ifaceArr of device.configDescriptor.interfaces) {
      for (const iface of ifaceArr) {
        // bInterfaceClass 0x0B is sometimes used for smart card readers.
        if (iface.bInterfaceClass === 0x0b) {
          return true;
        }
      }
    }
  }
  return false;
}

function getPortFromUniqueKey(uniqueKey) {
  const index = uniqueKey.lastIndexOf("-port");
  return index !== -1 ? uniqueKey.substring(index + 5) : "Unknown";
}

// Checks for connected scanners and emits events using the in-memory assignments.
function checkScanners(socket, deviceFingerprint) {
  const currentConnected = new Set();
  const roles = getAssignments(deviceFingerprint);

  usb.getDeviceList().forEach((device) => {
    if (isNfcScanner(device)) {
      const portPath = device.portNumbers
        ? device.portNumbers.join(".")
        : "Unknown";
      const uniqueKey = `${deviceFingerprint}-port${portPath}`;
      currentConnected.add(uniqueKey);

      if (roles[uniqueKey]) {
        socket.to(deviceFingerprint).emit("scannerDetected", {
          uniqueKey,
          portPath,
          assigned: true,
          role: roles[uniqueKey],
          online: true,
        });
      } else {
        // Only emit unassigned if less than 2 roles are assigned.
        if (Object.keys(roles).length < 2) {
          socket.to(deviceFingerprint).emit("scannerDetected", {
            uniqueKey,
            portPath,
            assigned: false,
            online: true,
          });
        }
      }
    }
  });

  // Emit events for scanners that are offline.
  Object.keys(roles).forEach((uniqueKey) => {
    if (
      uniqueKey.startsWith(deviceFingerprint) &&
      !currentConnected.has(uniqueKey)
    ) {
      const portPath = getPortFromUniqueKey(uniqueKey);
      socket.to(deviceFingerprint).emit("scannerDetected", {
        uniqueKey,
        portPath,
        assigned: true,
        role: roles[uniqueKey],
        online: false,
      });
    }
  });
}

module.exports = {
  getAssignments,
  setAssignment,
  removeAssignment,
  isNfcScanner: isNfcScanner, // exporting the generic NFC scanner detection
  getPortFromUniqueKey,
  checkScanners,
};
