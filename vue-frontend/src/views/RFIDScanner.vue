<template>
  <div class="h-screen w-full">
    <!-- Loading state while checking registration -->
    <div v-if="checkingRegistration" class="flex items-center justify-center h-full">
      <p>Checking device registration...</p>
    </div>

    <!-- Once registration check is done -->
    <div v-else>
      <!-- Main page view: display full layout if device is registered -->
      <div v-if="isRegistered" class="flex flex-col lg:flex-row h-screen w-full relative">
        <!-- Left Section -->
        <div class="w-full lg:w-2/5">
          <DaisyTimeIn :deviceFingerprint="deviceFingerprint" />
        </div>
        <!-- Right Section (Custom Carousel) -->
        <AnnouncementsCarousel />

        <!-- Port Info Button (fixed at top-right) -->
        <button @click="openPortStatusModal"
          class="fixed top-4 right-4 bg-[#198754] text-white p-3 rounded-full flex items-center justify-center">
          <i class="fas fa-info-circle text-2xl"></i>
        </button>
      </div>

      <!-- Registration view: show only when not registered -->
      <div v-else>
        <DeviceRegistration @registered="handleRegistered" />
      </div>
    </div>

    <!-- Port Status Modal including assignment UI -->
    <PortStatus v-if="showPortStatusModal" :deviceName="deviceName" :timeInInfo="timeInInfo" :timeOutInfo="timeOutInfo"
      :newScannerInfo="newScannerInfo" @close="closePortStatusModal" @assignRole="assignRole" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { initializeSocket, getSocket } from '@/composables/socket';
import HTTP from '@/http';
import AnnouncementsCarousel from "@/components/AnnouncementsCarousel.vue";
import DaisyTimeIn from "@/components/DaisyTimeIn.vue";
import DeviceRegistration from "@/components/DeviceRegistration.vue";
import PortStatus from "@/components/PortStatus.vue";

const isRegistered = ref(false);
const deviceName = ref('');
const deviceFingerprint = ref('');

const checkingRegistration = ref(true);

// Initially, try to get the socket instance (might be null if not initialized yet)
let socket = getSocket();

const showPortStatusModal = ref(false);

// Holds current port status info
const timeInInfo = ref(null);
const timeOutInfo = ref(null);

// Holds new scanner info if a scanner is detected and not assigned
const newScannerInfo = ref({ uniqueKey: '', portPath: '' });

onMounted(() => {
  checkRegistration();
});

async function checkRegistration() {
  try {
    const response = await HTTP.get('/api/device/status', { withCredentials: true });
    if (response.data) {
      if (response.data.device_name) {
        deviceName.value = response.data.device_name;
      }
      // If a device fingerprint exists, initialize the shared socket connection.
      if (response.data.device_fingerprint) {
        deviceFingerprint.value = response.data.device_fingerprint;
        initializeSocket(deviceFingerprint.value);
        // Update local socket variable from our composable.
        socket = getSocket();
        setupSocketListeners();
      }
      isRegistered.value = true;
    }
  } catch (error) {
    isRegistered.value = false;
  } finally {
    checkingRegistration.value = false;
  }
}

function handleRegistered(payload) {
  // Called when DeviceRegistration emits the 'registered' event.
  deviceName.value = payload.deviceName;
  if (payload.deviceFingerprint) {
    deviceFingerprint.value = payload.deviceFingerprint;
    // Initialize the shared socket connection for the newly registered device.
    initializeSocket(payload.deviceFingerprint);
    socket = getSocket();
    setupSocketListeners();
  }
  isRegistered.value = true;
}

function setupSocketListeners() {
  if (!socket) return;

  socket.on('connect', () => {
    console.log("Socket connected");
  });

  socket.on('scannerDetected', (data) => {
    if (!data.assigned) {
      newScannerInfo.value = data;
      // Optionally open the port status modal automatically if desired.
      showPortStatusModal.value = true;
    } else {
      if (data.role === 'Time In') {
        timeInInfo.value = data;
      } else if (data.role === 'Time Out') {
        timeOutInfo.value = data;
      }
    }
  });

  socket.on('scannerAssigned', (data) => {
    // Clear newScannerInfo after assignment.
    newScannerInfo.value = { uniqueKey: '', portPath: '' };
    if (data.role === 'Time In') {
      timeInInfo.value = data;
    } else if (data.role === 'Time Out') {
      timeOutInfo.value = data;
    }
  });

  socket.on('scannerDisconnected', (data) => {
    if (timeInInfo.value && timeInInfo.value.uniqueKey === data.uniqueKey) {
      timeInInfo.value.online = false;
    }
    if (timeOutInfo.value && timeOutInfo.value.uniqueKey === data.uniqueKey) {
      timeOutInfo.value.online = false;
    }
  });
}

function assignRole(role) {
  if (socket) {
    socket.emit('assignRole', { uniqueKey: newScannerInfo.value.uniqueKey, role });
  }
}

function openPortStatusModal() {
  showPortStatusModal.value = true;
}

function closePortStatusModal() {
  showPortStatusModal.value = false;
}
</script>
