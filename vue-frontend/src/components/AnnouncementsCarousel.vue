<template>
  <div class="w-full relative min-h-screen" :style="{
    backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1)), url(${bgAnnouncement})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }">

    <!-- Updated Header with bgAnnouncement -->
    <header class="w-full px-6 h-20 flex items-center relative" :style="{
      backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1)), url(${pncBg})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    }">
      <h1 class="text-white text-5xl font-bold mx-auto">Announcements</h1>
      <button @click="openPortStatusModal"
        class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-blue-800 text-white p-3 rounded-full flex items-center justify-center">
        <i class="fas fa-info-circle text-2xl"></i>
      </button>
    </header>

    <!-- Carousel Section -->
    <main class="box-shadow container mx-auto flex justify-center mt-10">
      <div class="rounded-lg shadow-lg w-full max-auto">
        <!-- Main Slider with Creative (Counterflow) Effect -->
        <Swiper :modules="modules" effect="creative" :creativeEffect="creativeEffect"
          :autoplay="{ delay: 1500, disableOnInteraction: false }" :thumbs="{ swiper: thumbsSwiper }" class="mySwiper2"
          @slideChange="updateActiveIndex">
          <SwiperSlide v-for="(announcement, index) in announcements" :key="index">
            <div class="w-full h-96 flex justify-center items-center">
              <DaisyCardAnnouncement :announcement="announcement" />
            </div>
          </SwiperSlide>
        </Swiper>

        <!-- Thumbnail (Preview) Slider -->
        <Swiper :modules="[FreeMode, Thumbs]" @swiper="onThumbsSwiper" :slides-per-view="4" :space-between="10"
          freeMode="true" watchSlidesProgress="true" centeredSlides="true" slideToClickedSlide="true"
          class="mySwiperThumbs mt-4">
          <SwiperSlide v-for="(announcement, index) in announcements" :key="index"
            :class="{ 'opacity-100': index === activeIndex, 'opacity-50': index !== activeIndex }">
            <div v-if="announcement.type === 'image'" class="cursor-pointer">
              <img :src="apiUrl + announcement.content.file_path" alt="preview"
                class="w-full object-cover h-24 rounded border border-gray-200 shadow-md" />
            </div>
            <div v-else-if="announcement.type === 'text'" class="cursor-pointer">
              <div
                class="w-full h-24 rounded flex items-center justify-center text-xs text-white bg-cover bg-center border border-gray-200 shadow-md"
                :style="{ backgroundImage: `url(${pncBg})` }">
                <span class="p-1 text-center">{{ announcement.content.title }}</span>
              </div>
            </div>
          </SwiperSlide>
        </Swiper>

      </div>
    </main>

    <!-- Port Status Modal -->
    <PortStatus v-if="showPortStatusModal" :deviceName="deviceName" :timeInInfo="timeInInfo" :timeOutInfo="timeOutInfo"
      :newScannerInfo="newScannerInfo" @close="closePortStatusModal" @assignRole="assignRole" />

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { EffectCreative, Autoplay, Thumbs, FreeMode } from "swiper/modules";
import "swiper/css";
import "swiper/css/effect-creative";
import "swiper/css/free-mode";
import HTTP from "@/http";
import pncBg from "@/assets/img/pnc-bg.jpg";
import bgAnnouncement from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/Daisy/DaisyCardAnnouncement.vue";
import PortStatus from "@/components/PortStatus.vue";
import { initializeSocket, getSocket } from "@/composables/socket";

const props = defineProps({
  deviceName: { type: String, required: true },
});

const announcements = ref([]);
const showPortStatusModal = ref(false);
const timeInInfo = ref(null);
const timeOutInfo = ref(null);
const newScannerInfo = ref({ uniqueKey: "", portPath: "" });
const socket = ref(getSocket());
const apiUrl = import.meta.env.VITE_API_URL;

const activeIndex = ref(0);

const updateActiveIndex = (swiper) => {
  activeIndex.value = swiper.realIndex;
};


// Include the Creative effect module in your modules array
const modules = [EffectCreative, Autoplay, Thumbs];

// Define the creative effect for a counterflow-like transition
const creativeEffect = {
  prev: {
    translate: ["-120%", 0, -500],
    scale: 0.8,
    opacity: 0.5,
  },
  next: {
    translate: ["120%", 0, -500],
    scale: 0.8,
    opacity: 0.5,
  },
};

// Ref to hold the thumbs swiper instance
const thumbsSwiper = ref(null);
function onThumbsSwiper(swiper) {
  thumbsSwiper.value = swiper;
}

const fetchAnnouncements = async () => {
  try {
    const response = await HTTP.get("/api/announcements");
    announcements.value = response.data.announcements;
  } catch (error) {
    console.error("Error fetching announcements:", error);
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
    socket.value.emit("assignRole", { uniqueKey: newScannerInfo.value.uniqueKey, role });
  }
}

function setupSocketListeners() {
  if (!socket.value) return;
  socket.value.on("connect", () => {
    console.log("Socket connected");
  });
  socket.value.on("scannerDetected", (data) => {
    if (!data.assigned) {
      newScannerInfo.value = data;
      showPortStatusModal.value = true;
    } else {
      if (data.role === "Time In") {
        timeInInfo.value = data;
      } else if (data.role === "Time Out") {
        timeOutInfo.value = data;
      }
    }
  });
  socket.value.on("scannerAssigned", (data) => {
    newScannerInfo.value = { uniqueKey: "", portPath: "" };
    if (data.role === "Time In") {
      timeInInfo.value = data;
    } else if (data.role === "Time Out") {
      timeOutInfo.value = data;
    }
  });
  socket.value.on("scannerDisconnected", (data) => {
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
  if (!socket.value) {
    initializeSocket();
    socket.value = getSocket();
  }
  setupSocketListeners();
});

onUnmounted(() => {
  // Clean up socket listeners if necessary
});
</script>

<style scoped>
.mySwiperThumbs .swiper-slide {
  cursor: pointer;
}
</style>
