// simpleCardScan.js
const { NFC } = require("nfc-pcsc");

const nfc = new NFC();

nfc.on("reader", (reader) => {
  // Set an initial custom status for your own tracking.
  reader.customStatus = "OUT"; // Initially no card present

  console.log(`Reader detected: ${reader.name}`);
  console.log("Reader device info:", reader);

  // Try to extract USB port information from the underlying device
  let portInfo = "Unknown port";
  if (
    reader.device &&
    reader.device.portNumbers &&
    reader.device.portNumbers.length
  ) {
    portInfo = reader.device.portNumbers.join(".");
  } else if (reader.device) {
    portInfo = `bus${reader.device.busNumber}-addr${reader.device.deviceAddress}`;
  }
  console.log(`Port info: ${portInfo}`);

  // When a card is detected, update customStatus to "IN" and log details.
  reader.on("card", (card) => {
    reader.customStatus = "IN";
    console.log(`Card detected on port ${portInfo}: UID ${card.uid}`);
    console.log(`Custom Status: ${reader.customStatus}`);
    console.log("Reader device info:", reader);
  });

  // If the library emits a "card.off" event when the card is removed,
  // you can update the status back to "OUT". (Check if your version supports this event.)
  reader.on("card.off", (card) => {
    reader.customStatus = "OUT";
    console.log(
      `Card removed on port ${portInfo}. Custom Status: ${reader.customStatus}`
    );
  });

  reader.on("error", (err) => {
    console.error(`Error on reader ${reader.name}: ${err.message}`);
  });
});

nfc.on("error", (err) => {
  console.error(`NFC error: ${err.message}`);
});
