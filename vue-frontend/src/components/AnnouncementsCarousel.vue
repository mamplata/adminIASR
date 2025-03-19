<template>
  <div class="w-full relative min-h-screen" :style="{
    backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.1)), url(${bgAnnouncement})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }">
    <!-- Header -->
    <header class="w-full px-6 h-24 flex items-center relative" :style="{
      backgroundImage: `linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.1)), url(${pncBg})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    }">
      <h1 class="text-white text-6xl font-bold mx-auto">Announcements</h1>
      <button @click="openPortStatusModal"
        class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-[#20714c] text-white p-3 rounded-full flex items-center justify-center">
        <i class="fas fa-info-circle text-2xl"></i>
      </button>
    </header>

    <!-- Main Carousel Section -->
    <main class="container mx-auto flex items-center justify-center" style="min-height: calc(100vh - 6rem);">
      <div class="rounded-lg shadow-lg w-full mx-auto">
        <!-- Main Slider with Two Columns -->
        <Swiper :modules="[EffectCreative, Autoplay, Thumbs]" :autoplay="{ delay: 1500, disableOnInteraction: false }"
          :slides-per-view="2" :space-between="20" :thumbs="{ swiper: thumbsSwiperComputed }" class="mySwiper2"
          @swiper="setMainSwiper" @slideChange="updateActiveIndex">
          <SwiperSlide v-for="(announcement, index) in announcements" :key="index">
            <div class="w-full h-96 flex justify-center items-center">
              <DaisyCardAnnouncement :announcement="announcement" class="w-full h-full" />
            </div>
          </SwiperSlide>
        </Swiper>

        <!-- Thumbnail Preview Slider -->
        <Swiper :modules="[FreeMode, Thumbs]" @swiper="setThumbsSwiper" :slides-per-view="4" :space-between="10"
          freeMode watchSlidesProgress centeredSlides slideToClickedSlide class="mySwiperThumbs mt-5">
          <SwiperSlide v-for="(announcement, index) in announcements" :key="index"
            :class="{ 'opacity-100': index === activeIndex, 'opacity-50': index !== activeIndex }">
            <div class="cursor-pointer">
              <img v-if="announcement.type === 'image'" :src="apiUrl + announcement.content.file_path" alt="preview"
                class="w-full h-40 object-cover rounded border border-gray-200 shadow-md" />
              <div v-else
                class="w-full h-40 rounded flex items-center justify-center text-xs text-white bg-cover bg-center border border-gray-200 shadow-md"
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
import { ref, computed, onMounted } from "vue";
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

// Swiper references
const mainSwiper = ref(null);
const thumbsSwiper = ref(null);
const activeIndex = ref(0);

// Set swiper instances
const setMainSwiper = (swiper) => {
  mainSwiper.value = swiper;
};
const setThumbsSwiper = (swiper) => {
  thumbsSwiper.value = swiper;
};

// Update active index on slide change
const updateActiveIndex = (swiper) => {
  activeIndex.value = swiper.realIndex;
};

// Computed thumbs instance (only if valid)
const thumbsSwiperComputed = computed(() => {
  return thumbsSwiper.value && !thumbsSwiper.value.destroyed ? thumbsSwiper.value : null;
});

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
</script>

<style scoped>
.mySwiper2 .swiper-slide {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.mySwiper2 .swiper-slide img {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 8px;
}
</style>
