<script setup>
import { ref } from 'vue';
import { io } from 'socket.io-client';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';

// Initialize socket connection to your Node server
const socket = io("http://localhost:3000");

// Reactive variables for the fingerprint details
const machineId = ref("");
const hardwareUUID = ref("");
const macAddress = ref("");
const deviceFingerprint = ref("");
const deviceName = ref(""); // New reactive variable for the device name

// Listen for the 'deviceInfo' event from the server
socket.on("deviceInfo", (data) => {
  machineId.value = data.machineId;
  hardwareUUID.value = data.hardwareUUID;
  macAddress.value = data.macAddress;
  deviceFingerprint.value = data.deviceFingerprint;
  deviceName.value = data.deviceName; // Update deviceName
});

// Emit the event when the button is clicked
function fetchDeviceInfo() {
  socket.emit("getDeviceInfo");
}
</script>

<template>
  <AuthenticatedLayout title="Dashboard">
    <template #header>
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <h2 class="text-xl font-semibold leading-tight">
          Devices
        </h2>
      </div>
    </template>

    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
      <button @click="fetchDeviceInfo" class="bg-blue-500 text-white px-4 py-2 rounded">
        Get Device Info
      </button>
      <div class="mt-4">
        <p><strong>Machine ID:</strong> {{ machineId }}</p>
        <p><strong>Hardware UUID:</strong> {{ hardwareUUID }}</p>
        <p><strong>MAC Address:</strong> {{ macAddress }}</p>
        <p><strong>Device Fingerprint:</strong> {{ deviceFingerprint }}</p>
        <p><strong>Device Name:</strong> {{ deviceName }}</p> <!-- Display device name -->
      </div>
    </div>
  </AuthenticatedLayout>
</template>
