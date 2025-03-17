<template>
  <div class="w-full h-full lg:h-full relative overflow-hidden"
    :style="{ backgroundImage: `url(${bgAnnounce})`, backgroundSize: 'cover', backgroundPosition: 'center' }">
    <!-- Header with Announcements title and Port Info Button -->
    <div class="absolute top-0 left-0 right-0 z-10">
      <div class="bg-[#198754] w-full px-6 h-24 flex items-center relative">
        <!-- Centered Announcement Text -->
        <h1 class="text-white text-5xl font-bold mx-auto">Announcements</h1>
        <!-- Port Info Button, absolutely positioned -->
        <button @click="openPortStatusModal"
          class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-blue-800 text-white p-3 rounded-full flex items-center justify-center">
          <i class="fas fa-info-circle text-2xl"></i>
        </button>
      </div>
    </div>
    <!-- Content Section -->
    <div class="w-full h-full overflow-hidden relative flex justify-center items-center">
      <div class="w-full h-full overflow-hidden relative">
        <div class="flex w-full h-full" :style="containerStyle">
          <div v-for="(announcement, index) in slides" :key="index" class="flex-none w-full h-full">
            <DaisyCardAnnouncement :announcement="announcement" />
          </div>
        </div>
      </div>
    </div>
    <!-- Port Status Modal -->
    <PortStatus v-if="showPortStatusModal" :deviceName="deviceName" :timeInInfo="timeInInfo" :timeOutInfo="timeOutInfo"
      :newScannerInfo="newScannerInfo" @close="closePortStatusModal" @assignRole="assignRole" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import HTTP from "@/http";
import bgAnnounce from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/Daisy/DaisyCardAnnouncement.vue";
import PortStatus from "@/components/PortStatus.vue";
import { initializeSocket, getSocket } from '@/composables/socket';

const announcements = ref([]);
const currentIndex = ref(0);
const disableTransition = ref(false);
let timer = null;

// Port status state
const showPortStatusModal = ref(false);
const timeInInfo = ref(null);
const timeOutInfo = ref(null);
const newScannerInfo = ref({ uniqueKey: '', portPath: '' });
const deviceName = ref('');

// Socket instance
const socket = ref(getSocket());

// Fetch announcements
const fetchAnnouncements = async () => {
  try {
    const response = await HTTP.get("/api/announcements");
    announcements.value = response.data.announcements;
  } catch (error) {
    console.error("Error fetching announcements:", error);
  }
};

// Duplicate first slide for smooth infinite loop
const slides = computed(() =>
  announcements.value.length > 0 ? [...announcements.value, announcements.value[0]] : []
);

const containerStyle = computed(() => ({
  transform: `translateX(-${currentIndex.value * 100}%)`,
  transition: disableTransition.value ? "none" : "transform 0.5s ease-in-out",
}));

const nextSlide = () => {
  currentIndex.value++;
  if (currentIndex.value >= announcements.value.length) {
    setTimeout(() => {
      disableTransition.value = true;
      currentIndex.value = 0;
      setTimeout(() => (disableTransition.value = false), 50);
    }, 500);
  }
};

function openPortStatusModal() {
  showPortStatusModal.value = true;
}

function closePortStatusModal() {
  showPortStatusModal.value = false;
}

function assignRole(role) {
  if (socket.value) {
    socket.value.emit('assignRole', { uniqueKey: newScannerInfo.value.uniqueKey, role });
  }
}

function setupSocketListeners() {
  if (!socket.value) return;

  socket.value.on('connect', () => {
    console.log("Socket connected");
  });

  socket.value.on('scannerDetected', (data) => {
    if (!data.assigned) {
      newScannerInfo.value = data;
      showPortStatusModal.value = true;
    } else {
      if (data.role === 'Time In') {
        timeInInfo.value = data;
      } else if (data.role === 'Time Out') {
        timeOutInfo.value = data;
      }
    }
  });

  socket.value.on('scannerAssigned', (data) => {
    newScannerInfo.value = { uniqueKey: '', portPath: '' };
    if (data.role === 'Time In') {
      timeInInfo.value = data;
    } else if (data.role === 'Time Out') {
      timeOutInfo.value = data;
    }
  });

  socket.value.on('scannerDisconnected', (data) => {
    if (timeInInfo.value && timeInInfo.value.uniqueKey === data.uniqueKey) {
      timeInInfo.value.online = false;
    }
    if (timeOutInfo.value && timeOutInfo.value.uniqueKey === data.uniqueKey) {
      timeOutInfo.value.online = false;
    }
  });
}

onMounted(async () => {
  await fetchAnnouncements();
  timer = setInterval(nextSlide, 3000);

  // Initialize socket if not already initialized
  if (!socket.value) {
    initializeSocket();
    socket.value = getSocket();
  }
  setupSocketListeners();
});

onUnmounted(() => clearInterval(timer));
</script>
