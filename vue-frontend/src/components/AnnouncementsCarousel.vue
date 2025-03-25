<template>
  <div class="w-full relative min-h-screen" :style="{
    backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.1)), url(${bgAnnouncement})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }">
    <!-- Header -->
    <header class="w-full px-6 h-24 flex items-center relative" :style="{ backgroundColor: '#198754' }">
      <h1 class="text-white text-6xl font-bold mx-auto">Announcements</h1>
      <button @click="scannerPortStore.openPortStatusModal()"
        class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-blue-800 text-white p-3 rounded-full flex items-center justify-center">
        <i class="fas fa-info-circle text-2xl"></i>
      </button>
    </header>

    <!-- Main Content: Slider and Thumbnails -->
    <main class="w-full flex flex-col" style="height: calc(100vh - 6rem);">
      <!-- Main Slider Container -->
      <div class="main-slider-container w-full relative" style="height: calc(100% - 10rem);">
        <div v-if="announcementStore.loading" class="w-full h-full flex items-center justify-center">
          <p class="text-white text-2xl text-center">Loading announcements...</p>
        </div>
        <div v-else-if="announcementStore.filteredAnnouncements.length > 0" class="relative w-full h-full">
          <Swiper :key="swiperKey" ref="mainSwiper" :modules="[Autoplay, Thumbs, EffectFade]" effect="fade"
            :fadeEffect="{ crossFade: true }" :speed="3000" :slidesPerView="1"
            :autoplay="{ delay: 3000, disableOnInteraction: false }"
            :loop="announcementStore.filteredAnnouncements.length > 1"
            :thumbs="{ swiper: announcementStore.thumbsSwiper }" @swiper="announcementStore.setMainSwiper"
            @slideChangeTransitionStart="announcementStore.onSlideChangeTransitionStart"
            @slideChangeTransitionEnd="announcementStore.onSlideChangeTransitionEnd" class="mySwiper">
            <SwiperSlide v-for="(announcement, index) in announcementStore.filteredAnnouncements"
              :key="announcement.id || index" class="w-full h-full">
              <DaisyCardAnnouncement :index="index" :announcement="announcement" :isThumb="false"
                class="w-full h-full" />
            </SwiperSlide>
          </Swiper>
          <div class="black-overlay" :style="{ opacity: announcementStore.overlayOpacity }"></div>
        </div>
        <div v-else class="w-full h-full flex items-center justify-center">
          <div class="w-full bg-white bg-opacity-90 rounded-lg shadow-lg p-10 mx-10">
            <p class="text-gray-800 text-2xl text-center">No Announcements Available</p>
          </div>
        </div>
      </div>

      <!-- Thumbs Slider Container -->
      <div class="thumb-slider-container w-full" style="height: 10rem;"
        v-if="!announcementStore.loading && announcementStore.filteredAnnouncements.length > 0">
        <Swiper :modules="[Thumbs]"
          :slidesPerView="announcementStore.filteredAnnouncements.length < 3 ? announcementStore.filteredAnnouncements.length : 4"
          :centeredSlides="announcementStore.filteredAnnouncements.length === 1"
          :centerInsufficientSlides="announcementStore.filteredAnnouncements.length < 3" watchSlidesVisibility
          watchSlidesProgress @swiper="announcementStore.setThumbsSwiper" class="mySwiperThumbs">
          <SwiperSlide v-for="(announcement, index) in announcementStore.filteredAnnouncements"
            :key="'thumb-' + (announcement.id || index)" class="thumb">
            <DaisyCardAnnouncement :index="index" :announcement="announcement" :isThumb="true" />
          </SwiperSlide>
        </Swiper>
      </div>
    </main>

    <!-- Modal Components -->
    <ScannerAssignment />
    <PortStatus v-if="scannerPortStore.isPortStatusModalOpen" />
  </div>
</template>

<script setup>
import { onMounted, watch, ref } from "vue";
import bgAnnouncement from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/Daisy/DaisyCardAnnouncement.vue";
import PortStatus from "@/components/PortStatus.vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/thumbs";
import "swiper/css/effect-fade";
import { Autoplay, Thumbs, EffectFade } from "swiper/modules";
import ScannerAssignment from "./ScannerAssignment.vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";
import { useAnnouncementStore } from "@/stores/announcementStore";
import { useTimeInStore } from "@/stores/timeInStore";

const scannerPortStore = useScannerPortStore();
const announcementStore = useAnnouncementStore();
const timeInStore = useTimeInStore();

// On mount, fetch announcements if not already done
onMounted(async () => {
  if (announcementStore.announcements.length === 0) {
    await announcementStore.fetchAnnouncements();
  }
});

const swiperKey = ref(Date.now());

watch(
  () => timeInStore.selectedDepartment,
  (newDept) => {
    announcementStore.filterAnnouncements(newDept);
    // Update the key to force re-render of the Swiper
    swiperKey.value = Date.now();
  },
  { immediate: true }
);
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

.mySwiperThumbs :deep(.swiper-slide) {
  opacity: 0.5;
  transition: opacity 0.3s ease-in-out;
}

.mySwiperThumbs :deep(.swiper-slide-thumb-active) {
  opacity: 1 !important;
}

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
