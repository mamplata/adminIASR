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
          <DaisyTimeIn :deviceName="deviceName" :isRegistered="isRegistered" />
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

    <!-- Modal for scanner assignment using daisyUI -->
    <div v-if="showModal" class="modal modal-open">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Assign Scanner Role</h3>
        <p class="py-2"><strong>Scanner:</strong> {{ newScannerInfo.uniqueKey }}</p>
        <p class="py-2"><strong>Port:</strong> {{ newScannerInfo.portPath }}</p>
        <p class="py-2">Please choose a role:</p>
        <div class="modal-action">
          <button @click="assignRole('Time In')" class="btn btn-success">Time In</button>
          <button @click="assignRole('Time Out')" class="btn btn-warning">Time Out</button>
        </div>
      </div>
    </div>

    <!-- Modal for port status info -->
    <div v-if="showPortStatusModal" class="modal modal-open">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Port and Device Status</h3>
        <p class="py-2"><strong>Device:</strong> {{ deviceName }}</p>
        <div class="py-2">
          <p>
            <strong>Time In: </strong>
            <span>
              <template v-if="timeInInfo">
                <template v-if="timeInInfo.online">
                  <i class="fas fa-check text-green-500 mr-1"></i>
                  Online - Port {{ timeInInfo.portPath }}
                </template>
                <template v-else>
                  <i class="fas fa-times text-red-500 mr-1"></i>
                  Offline - Port {{ timeInInfo.portPath }}
                </template>
              </template>
              <template v-else>
                <i class="fas fa-times text-red-500 mr-1"></i>
                Not Connected
              </template>
            </span>
          </p>
          <p>
            <strong>Time Out: </strong>
            <span>
              <template v-if="timeOutInfo">
                <template v-if="timeOutInfo.online">
                  <i class="fas fa-check text-green-500 mr-1"></i>
                  Online - Port {{ timeOutInfo.portPath }}
                </template>
                <template v-else>
                  <i class="fas fa-times text-red-500 mr-1"></i>
                  Offline - Port {{ timeOutInfo.portPath }}
                </template>
              </template>
              <template v-else>
                <i class="fas fa-times text-red-500 mr-1"></i>
                Not Connected
              </template>
            </span>
          </p>
        </div>
        <div class="modal-action">
          <button @click="closePortStatusModal" class="btn">Close</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { io } from 'socket.io-client';
import HTTP from '@/http';
import AnnouncementsCarousel from "@/components/AnnouncementsCarousel.vue";
import DaisyTimeIn from "@/components/DaisyTimeIn.vue";
import DeviceRegistration from "@/components/DeviceRegistration.vue";

const isRegistered = ref(false);
const deviceName = ref('');
const checkingRegistration = ref(true);
let socket = null;

const showModal = ref(false);
const newScannerInfo = ref({ uniqueKey: '', portPath: '' });

const showPortStatusModal = ref(false);
const timeInInfo = ref(null);
const timeOutInfo = ref(null);

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
      // If a device fingerprint exists, initialize the socket connection.
      if (response.data.device_fingerprint) {
        socket = io('http://localhost:4000', {
          query: { deviceFingerprint: response.data.device_fingerprint },
        });
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
    socket = io('http://localhost:4000', {
      query: { deviceFingerprint: payload.deviceFingerprint },
    });
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
      showModal.value = true;
    } else {
      if (data.role === 'Time In') {
        timeInInfo.value = data;
      } else if (data.role === 'Time Out') {
        timeOutInfo.value = data;
      }
    }
  });

  socket.on('scannerAssigned', (data) => {
    showModal.value = false;
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
