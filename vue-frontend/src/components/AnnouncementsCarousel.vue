<template>
  <div class="w-full relative min-h-screen" :style="{
    backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.1)), url(${bgAnnouncement})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }">
    <!-- Header -->
    <header class="w-full px-6 h-24 flex items-center relative" :style="{ backgroundColor: '#198754' }">
      <h1 class="text-white text-6xl font-bold mx-auto">
        Announcements
      </h1>
      <button @click="openPortStatusModal"
        class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-blue-800 text-white p-3 rounded-full flex items-center justify-center">
        <i class="fas fa-info-circle text-2xl"></i>
      </button>
    </header>

    <!-- Main Content: Slider and Thumbnails -->
    <main class="w-full flex flex-col" style="height: calc(100vh - 6rem);">
      <!-- Main Slider Container -->
      <div class="main-slider-container w-full relative" style="height: calc(100% - 10rem);">
        <!-- Check loading state -->
        <div v-if="loading" class="w-full h-full flex items-center justify-center">
          <!-- Optionally, replace this with a spinner -->
          <p class="text-white text-2xl text-center">Loading announcements...</p>
        </div>
        <!-- Show slider if announcements exist -->
        <div v-else-if="filteredAnnouncements.length > 0" class="relative w-full h-full">
          <Swiper :modules="[Autoplay, Thumbs, EffectFade]" effect="fade" :fadeEffect="{ crossFade: true }"
            :speed="3000" :slidesPerView="1" :autoplay="{ delay: 3000, disableOnInteraction: false }"
            :loop="filteredAnnouncements.length > 1" :thumbs="{ swiper: thumbsSwiper }"
            @slideChangeTransitionStart="onSlideChangeTransitionStart"
            @slideChangeTransitionEnd="onSlideChangeTransitionEnd" class="mySwiper">

            <SwiperSlide v-for="(announcement, index) in filteredAnnouncements" :key="announcement.id || index"
              class="w-full h-full">
              <DaisyCardAnnouncement :announcement="announcement" :isThumb="false" class="w-full h-full" />
            </SwiperSlide>
          </Swiper>
          <!-- Black Overlay for fade effect -->
          <div class="black-overlay" :style="{ opacity: whiteOverlayOpacity }"></div>
        </div>
        <!-- Fallback Card when no announcements are available -->
        <div v-else class="w-full h-full flex items-center justify-center">
          <div class="w-full bg-white bg-opacity-90 rounded-lg shadow-lg p-10 mx-10">
            <p class="text-gray-800 text-2xl text-center">No Announcements Available</p>
          </div>
        </div>
      </div>

      <!-- Thumbs Slider Container (only if announcements exist) -->
      <div class="thumb-slider-container w-full" style="height: 10rem;"
        v-if="!loading && filteredAnnouncements.length > 0">
        <Swiper :modules="[Thumbs]" slidesPerView="4" watchSlidesVisibility watchSlidesProgress
          :onSwiper="onThumbsSwiper" class="mySwiperThumbs">
          <SwiperSlide v-for="(announcement, index) in filteredAnnouncements"
            :key="'thumb-' + (announcement.id || index)" class="thumb">
            <DaisyCardAnnouncement :announcement="announcement" :isThumb="true" />
          </SwiperSlide>
        </Swiper>
      </div>
    </main>

    <!-- Live Scanner Assignment display -->
    <ScannerAssignment />
    <!-- Port Status Modal -->
    <PortStatus v-show="showPortStatusModal" :deviceName="deviceName" @close="closePortStatusModal" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import HTTP from "@/http";
import bgAnnouncement from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/Daisy/DaisyCardAnnouncement.vue";
import PortStatus from "@/components/PortStatus.vue";

// Import Swiper Vue components and styles
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/thumbs";
import "swiper/css/effect-fade";

// Import modules including the Fade effect
import { Autoplay, Thumbs, EffectFade } from "swiper/modules";
import ScannerAssignment from "./ScannerAssignment.vue";

const props = defineProps({
  deviceName: { type: String, required: true },
  selectedDepartment: { type: String, default: "GENERAL" },
});

const announcements = ref([]);
const loading = ref(true); // new loading state
const showPortStatusModal = ref(false);
const currentDepartment = ref("GENERAL");
const thumbsSwiper = ref(null);
const whiteOverlayOpacity = ref(0);

watch(() => props.selectedDepartment, (newDepartment) => {
  currentDepartment.value = newDepartment;
});

const filteredAnnouncements = computed(() => {
  return announcements.value.filter((announcement) => {
    // Split the departments string by colon and trim any whitespace
    const dept = announcement.departments.split(':')[0].trim();
    return dept === currentDepartment.value;
  });
});

watch([announcements, currentDepartment], () => {
  console.log(currentDepartment.value);
  console.log("Filtered announcements updated:", filteredAnnouncements.value);
});

watch(filteredAnnouncements, (newList) => {
  if (newList.length === 0 || newList.length === 1) {
    setTimeout(() => {
      currentDepartment.value = "GENERAL";

    }, 5000);
  }
});


const fetchAnnouncements = async () => {
  try {
    const response = await HTTP.get("/api/announcements");
    const fetchedAnnouncements = response.data.announcements || [];

    if (fetchedAnnouncements.length === 0) {
      currentDepartment.value = "GENERAL";
    }

    announcements.value = fetchedAnnouncements;
  } catch (error) {
    console.error("Error fetching announcements:", error);
  } finally {
    loading.value = false;
  }
};


function openPortStatusModal() {
  showPortStatusModal.value = true;
}

function closePortStatusModal() {
  showPortStatusModal.value = false;
}

function onThumbsSwiper(swiper) {
  thumbsSwiper.value = swiper;
}

function onSlideChangeTransitionStart() {
  // Trigger the black overlay to appear during the transition
  whiteOverlayOpacity.value = 1;
}

function onSlideChangeTransitionEnd(swiper) {
  whiteOverlayOpacity.value = 0;
  // If it's the last slide, also reset
  if (swiper.isEnd) {
    setTimeout(() => {
      currentDepartment.value = "GENERAL";
    }, 500);
  }
}

onMounted(async () => {
  await fetchAnnouncements();
});
</script>

<style scoped>
.mySwiper {
  width: 100%;
  height: 100%;
  position: relative;
}

.mySwiperThumbs {
  width: 100%;
  height: 100%;
}

.mySwiperThumbs ::v-deep .swiper-slide {
  opacity: 0.5;
  transition: opacity 0.3s ease-in-out;
}

.mySwiperThumbs ::v-deep .swiper-slide-thumb-active {
  opacity: 1 !important;
}

/* Black overlay to create a "black fade" effect */
.black-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: #000;
  pointer-events: none;
  transition: opacity 4s ease-in-out;
}
</style>
