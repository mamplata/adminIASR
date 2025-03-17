<template>
  <div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Left Section -->
    <div class="w-full lg:w-2/5 flex flex-col justify-center p-4">
      <DaisyTimeIn />
      <!-- Device Registration and Port Management Status -->
      <div class="mt-4">
        <div v-if="checkingRegistration" class="text-center">
          <span class="text-lg font-semibold">Checking device registration...</span>
        </div>
        <div v-else-if="!isRegistered" class="card w-full bg-base-200 shadow-xl p-4">
          <h2 class="card-title mb-4">Register Device</h2>
          <form @submit.prevent="registerDevice" class="space-y-4">
            <input v-model="shortCode" type="text" placeholder="Enter short code" class="input input-bordered w-full"
              required />
            <button type="submit" class="btn btn-success w-full">Register</button>
          </form>
          <p v-if="errorMessage" class="text-red-500 mt-2">{{ errorMessage }}</p>
        </div>
        <div v-else class="text-center">
          <p class="text-lg">
            Device Registered: <strong>{{ deviceName }}</strong>
          </p>
          <p class="text-md text-gray-700">{{ scannerStatus }}</p>
        </div>
      </div>
    </div>

    <!-- Right Section (Custom Carousel) -->
    <div class="w-full lg:w-3/5 relative overflow-hidden flex justify-center items-center"
      :style="{ backgroundImage: `url(${bgAnnounce})`, backgroundSize: 'cover', backgroundPosition: 'center' }">
      <div class="w-full h-[400px] lg:h-[500px] overflow-hidden relative">
        <!-- Carousel Container -->
        <div class="flex w-full h-full" :style="containerStyle">
          <!-- Slides using DaisyCardAnnouncement for both text and image types -->
          <div v-for="(announcement, index) in slides" :key="index" class="flex-none w-full h-full">
            <DaisyCardAnnouncement :announcement="announcement" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { io } from "socket.io-client";
import HTTP from "@/http"; // Axios instance
import bgAnnounce from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/DaisyCardAnnouncement.vue";
import DaisyTimeIn from "@/components/DaisyTimeIn.vue";

// =====================
// Announcement Carousel
// =====================
const announcements = ref([]);
const currentIndex = ref(0);
const disableTransition = ref(false);
let timer = null;

const fetchAnnouncements = async () => {
  try {
    const response = await HTTP.get("/api/announcements");
    announcements.value = response.data.announcements;
  } catch (error) {
    console.error("Error fetching announcements:", error);
  }
};

const slides = computed(() =>
  announcements.value.length > 0
    ? [...announcements.value, announcements.value[0]]
    : []
);

const containerStyle = computed(() => ({
  transform: `translateX(-${currentIndex.value * 100}%)`,
  transition: disableTransition.value ? "none" : "transform 0.5s ease-in-out",
}));

// =====================================
// Device Registration & Port Management
// =====================================
const shortCode = ref('');
const isRegistered = ref(false);
const deviceName = ref('');
const errorMessage = ref('');
const checkingRegistration = ref(true);

// NFC and Scanner States
const nfcData = ref(null);
const nfcError = ref('');
const isReadingNfc = ref(false);
const scannedStudent = ref(null);

const showModal = ref(false);
const newScannerInfo = ref({ uniqueKey: '', portPath: '' });
const scannerStatus = ref('');

let socket = null;

async function checkRegistration() {
  try {
    const response = await HTTP.get("/api/device/status", { withCredentials: true });
    if (response.data) {
      if (response.data.device_name) {
        deviceName.value = response.data.device_name;
      }
      if (response.data.device_fingerprint) {
        socket = io("http://localhost:4000", {
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
    if (isRegistered.value) {
      readNfcCard();
    }
  }
}

async function registerDevice() {
  errorMessage.value = '';
  try {
    const { data } = await HTTP.post(
      "/api/device/register",
      { short_code: shortCode.value },
      { withCredentials: true }
    );
    if (data && data.success) {
      deviceName.value = data.device_name || '';
      if (data.device_fingerprint) {
        socket = io("http://localhost:4000", {
          query: { deviceFingerprint: data.device_fingerprint },
        });
        setupSocketListeners();
      }
      isRegistered.value = true;
      readNfcCard();
    }
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "An unexpected error occurred.";
  }
}

function readNfcCard() {
  if (isReadingNfc.value) return;
  isReadingNfc.value = true;
  nfcData.value = null;
  nfcError.value = "";
  console.log("📡 Requesting to read NFC card...");
  if (socket) {
    socket.emit("readCard");
    socket.emit("getStoredAssignments");
  } else {
    console.error("Socket connection not established");
  }
}

async function processScannedCard(card) {
  try {
    const response = await HTTP.post(
      "/api/card/scan",
      { uid: card.uid, data: card.data },
      { withCredentials: true }
    );
    scannedStudent.value = response.data.student;
  } catch (err) {
    nfcError.value =
      err.response?.data?.error || "An error occurred during card scan.";
    scannedStudent.value = null;
  } finally {
    isReadingNfc.value = false;
    if (isRegistered.value) {
      setTimeout(() => {
        readNfcCard();
      }, 2000);
    }
  }
}

function setupSocketListeners() {
  if (!socket) return;
  socket.on("connect", () => {
    console.log("Socket connected");
  });

  socket.on("cardRead", (data) => {
    nfcData.value = data;
    processScannedCard(data);
  });

  socket.on("readFailed", (data) => {
    console.error("❌ NFC Read Failed:", data);
    nfcError.value = "Unauthorized access.";
    isReadingNfc.value = false;
    scannedStudent.value = null;
    if (isRegistered.value) {
      setTimeout(() => {
        readNfcCard();
      }, 2000);
    }
  });

  // Scanner assignment events
  socket.on("scannerDetected", (data) => {
    if (!data.assigned) {
      newScannerInfo.value = data;
      showModal.value = true;
    } else {
      scannerStatus.value = `Scanner ${data.uniqueKey} is assigned as ${data.role}.`;
    }
  });
  socket.on("scannerAssigned", (data) => {
    scannerStatus.value = `Scanner ${data.uniqueKey} assigned as ${data.role}.`;
    showModal.value = false;
  });
  socket.on("scannerAssignmentError", (data) => {
    scannerStatus.value = `Error assigning scanner ${data.uniqueKey}: ${data.message}`;
    showModal.value = false;
  });
  socket.on("scannerDisconnected", (data) => {
    scannerStatus.value = `Scanner disconnected: ${data.uniqueKey}`;
  });
}

function assignRole(role) {
  if (socket) {
    socket.emit("assignRole", {
      uniqueKey: newScannerInfo.value.uniqueKey,
      role,
    });
  }
}

function cancelAssignment() {
  showModal.value = false;
}

// =====================
// Combined Lifecycle Hooks
// =====================
onMounted(async () => {
  // Fetch announcements and start carousel timer
  await fetchAnnouncements();
  timer = setInterval(() => {
    currentIndex.value++;
    if (currentIndex.value >= announcements.value.length) {
      setTimeout(() => {
        disableTransition.value = true;
        currentIndex.value = 0;
        setTimeout(() => (disableTransition.value = false), 50);
      }, 500);
    }
  }, 3000);

  // Check device registration and manage port status
  await checkRegistration();
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>

<style scoped>
/* Additional styling if needed */
</style>
