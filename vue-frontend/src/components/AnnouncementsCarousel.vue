<template>
  <div class="w-full relative min-h-screen overflow-hidden" :style="carouselStyle">
    <!-- Header -->
    <header class="w-full px-6 h-24 flex items-center relative" style="background-color: #198754">
      <h1 class="text-white text-6xl font-bold mx-auto">Announcements</h1>
      <button @click="scannerPortStore.openPortStatusModal()"
        class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-blue-800 text-white p-3 rounded-full flex items-center justify-center">
        <i class="fas fa-info-circle text-2xl"></i>
      </button>
    </header>

    <!-- Main Content: Carousel and Thumbnails -->
    <main class="w-full flex flex-col relative" :style="{ height: `calc(100vh - 6rem)` }">
      <!-- Global Black Overlay -->
      <div class="absolute inset-0 bg-black opacity-40 z-10 pointer-events-none"></div>

      <!-- Carousel -->
      <div v-if="announcementStore.loading" class="w-full flex-1 flex items-center justify-center relative z-20">
        <p class="text-white text-2xl text-center">Loading announcements...</p>
      </div>
      <div v-else-if="announcementStore.filteredAnnouncements.length > 0" class="relative flex-1 z-20">
        <div v-for="(announcement, index) in announcementStore.filteredAnnouncements" :key="announcement.id || index"
          class="carousel-item relative w-full h-full"
          :class="{ active: index === announcementStore.activeIndex, inactive: index !== announcementStore.activeIndex }">
          <DaisyCardAnnouncement :index="index" :announcement="announcement" :isThumb="false" class="w-full h-full" />
        </div>
      </div>
      <div v-else class="w-full flex-1 flex items-center justify-center relative z-20">
        <div class="w-full bg-white bg-opacity-90 rounded-lg shadow-lg p-10 mx-10">
          <p class="text-gray-800 text-2xl text-center">No Announcements Available</p>
        </div>
      </div>

      <!-- Thumbnails Strip -->
      <div v-if="!announcementStore.loading && announcementStore.filteredAnnouncements.length > 0"
        class="thumbs mt-4 flex relative z-20">
        <div v-for="(announcement, index) in announcementStore.filteredAnnouncements"
          :key="'thumb-' + (announcement.id || index)" class="flex-1 cursor-pointer border-2" :class="{
            'border-blue-500': index === announcementStore.activeIndex,
            'border-gray-300': index !== announcementStore.activeIndex
          }" @click="handleThumbnailClick(index)">
          <DaisyCardAnnouncement :index="index" :announcement="announcement" :isThumb="true" thumbnailHeight="8rem" />
        </div>
      </div>
    </main>

    <!-- Modal Components -->
    <ScannerAssignment />
    <PortStatus v-show="scannerPortStore.isPortStatusModalOpen" />
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, watch, computed } from "vue";
import bgAnnouncement from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/Daisy/DaisyCardAnnouncement.vue";
import PortStatus from "@/components/PortStatus.vue";
import ScannerAssignment from "./ScannerAssignment.vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";
import { useAnnouncementStore } from "@/stores/announcementStore";
import { useTimeInStore } from "@/stores/timeInStore";

const scannerPortStore = useScannerPortStore();
const announcementStore = useAnnouncementStore();
const timeInStore = useTimeInStore();

const carouselStyle = computed(() => ({
  backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.1)), url(${bgAnnouncement})`,
  backgroundSize: "cover",
  backgroundPosition: "center"
}));

// Autoplay using centralized nextSlide from the store.
let autoplayInterval = null;
function startAutoplay() {
  autoplayInterval = setInterval(() => {
    const current = announcementStore.filteredAnnouncements[announcementStore.activeIndex];
    const needsScroll = announcementStore.scrollNeededStatus[announcementStore.activeIndex];
    // If it's a text announcement with scrolling, let the component's marquee finish.
    if (current && current.type === "text" && needsScroll) {
      // Do nothing; DaisyCardAnnouncement should trigger handleScrollFinished().
    } else {
      announcementStore.nextSlide();
    }
  }, 5000);
}

function stopAutoplay() {
  if (autoplayInterval) clearInterval(autoplayInterval);
}

onMounted(async () => {
  if (announcementStore.announcements.length === 0) {
    await announcementStore.fetchAnnouncements();
  }
  startAutoplay();
});

onBeforeUnmount(() => {
  stopAutoplay();
});

// When the department filter changes, reset announcements and active index.
watch(
  () => timeInStore.selectedDepartment,
  async (newDept) => {
    // First fetch the latest announcements from the server
    await announcementStore.fetchAnnouncements();
    // Then reset the active index (the fetch already filters based on newDept)
    announcementStore.setActiveIndex(0);
  },
  { immediate: true }
);

import { nextTick } from "vue";

function handleThumbnailClick(index) {
  stopAutoplay(); // Stop the autoplay timer
  announcementStore.setActiveIndex(index); // Change slide
  nextTick(() => {
    // Check if the current slide is text and needs scrolling
    const current = announcementStore.filteredAnnouncements[announcementStore.activeIndex];
    const needsScroll = announcementStore.scrollNeededStatus[announcementStore.activeIndex];
    if (current && current.type === "text" && needsScroll) {
      // Let the component's marquee finish (it will call handleScrollFinished)
      // Do not restart autoplay here.
    } else {
      // If no marquee is needed, restart autoplay after a delay.
      setTimeout(() => {
        startAutoplay();
      }, 5000);
    }
  });
}

</script>

<style scoped>
.carousel {
  height: calc(100% - 10rem);
  margin: 0;
  padding: 0;
}

/* Slow fade transition for carousel items */
.carousel-item {
  transition: opacity 3s ease-in-out;
  margin: 0;
  padding: 0;
}

.carousel-item.inactive {
  opacity: 0;
  pointer-events: none;
  position: absolute;
  top: 0;
  left: 0;
}

.carousel-item.active {
  opacity: 1;
  position: relative;
  margin: 0;
  padding: 0;
}

/* Remove margin/padding from thumbnails container */
.thumbs {
  margin: 0;
  padding: 0;
}

.thumbs>div {
  margin: 0 !important;
  padding: 0 !important;
}
</style>
