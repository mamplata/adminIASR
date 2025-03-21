  <template>
    <div class="w-full relative min-h-screen" :style="{
      backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.1)), url(${bgAnnouncement})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center'
    }">
      <!-- Header -->
      <header class="w-full px-6 h-24 flex items-center relative" :style="{ backgroundColor: '#198754' }">
        <h1 class="text-white text-6xl font-bold mx-auto">Announcements</h1>
        <button @click="openPortStatusModal"
          class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-blue-800 text-white p-3 rounded-full flex items-center justify-center">
          <i class="fas fa-info-circle text-2xl"></i>
        </button>
      </header>

      <!-- Main Content: Slider and Thumbnails -->
      <main class="w-full flex flex-col" style="height: calc(100vh - 6rem);">
        <!-- Main Slider Container -->
        <div class="main-slider-container w-full relative" style="height: calc(100% - 10rem);">
          <div v-if="loading" class="w-full h-full flex items-center justify-center">
            <p class="text-white text-2xl text-center">Loading announcements...</p>
          </div>
          <div v-else-if="filteredAnnouncements.length > 0" class="relative w-full h-full">
            <Swiper ref="mainSwiper" :modules="[Autoplay, Thumbs, EffectFade]" effect="fade"
              :fadeEffect="{ crossFade: true }" :speed="3000" :slidesPerView="1"
              :autoplay="{ delay: 3000, disableOnInteraction: false }" :loop="filteredAnnouncements.length > 1"
              :thumbs="{ swiper: thumbsSwiper }" @swiper="onMainSwiper"
              @slideChangeTransitionStart="onSlideChangeTransitionStart"
              @slideChangeTransitionEnd="onSlideChangeTransitionEnd" class="mySwiper">
              <SwiperSlide v-for="(announcement, index) in filteredAnnouncements" :key="announcement.id || index"
                class="w-full h-full">
                <DaisyCardAnnouncement :active="index === activeIndex" :announcement="announcement" :isThumb="false"
                  class="w-full h-full" />
              </SwiperSlide>
            </Swiper>
            <div class="black-overlay" :style="{ opacity: overlayOpacity }"></div>
          </div>
          <div v-else class="w-full h-full flex items-center justify-center">
            <div class="w-full bg-white bg-opacity-90 rounded-lg shadow-lg p-10 mx-10">
              <p class="text-gray-800 text-2xl text-center">No Announcements Available</p>
            </div>
          </div>
        </div>

        <!-- Thumbs Slider Container -->
        <div class="thumb-slider-container w-full" style="height: 10rem;"
          v-if="!loading && filteredAnnouncements.length > 0">
          <Swiper :modules="[Thumbs]"
            :slidesPerView="filteredAnnouncements.length < 3 ? filteredAnnouncements.length : 4"
            :centeredSlides="filteredAnnouncements.length === 1"
            :centerInsufficientSlides="filteredAnnouncements.length < 3" watchSlidesVisibility watchSlidesProgress
            :onSwiper="onThumbsSwiper" class="mySwiperThumbs">
            <SwiperSlide v-for="(announcement, index) in filteredAnnouncements"
              :key="'thumb-' + (announcement.id || index)" class="thumb">
              <DaisyCardAnnouncement :announcement="announcement" :isThumb="true" />
            </SwiperSlide>
          </Swiper>
        </div>
      </main>

      <ScannerAssignment />
      <PortStatus v-show="showPortStatusModal" :deviceName="deviceName" @close="closePortStatusModal" />
    </div>
  </template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import HTTP from "@/http";
import bgAnnouncement from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/Daisy/DaisyCardAnnouncement.vue";
import PortStatus from "@/components/PortStatus.vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/thumbs";
import "swiper/css/effect-fade";
import { Autoplay, Thumbs, EffectFade } from "swiper/modules";
import ScannerAssignment from "./ScannerAssignment.vue";

// Define the events that can be emitted to the parent.
const emit = defineEmits(["update:selectedDepartment"]);

const props = defineProps({
  deviceName: { type: String, required: true },
  selectedDepartment: { type: String, default: "GENERAL" },
});

const announcements = ref([]);
const loading = ref(true);
const showPortStatusModal = ref(false);
const thumbsSwiper = ref(null);
const overlayOpacity = ref(1);
const mainSwiper = ref(null);
const activeIndex = ref(0);

const filteredAnnouncements = computed(() => {
  return announcements.value.filter(announcement => {
    const announcementDepartments = announcement.departments.trim();

    // Handle GENERAL announcements
    if (announcementDepartments === "GENERAL") {
      return props.selectedDepartment.trim() === "GENERAL";
    }

    // Split multiple groups by semicolon
    const groups = announcementDepartments
      .split(";")
      .map(group => group.trim())
      .filter(Boolean);

    // Split the scanned selectedDepartment (e.g., "CCS: BSIT") into department and program.
    const selectedParts = props.selectedDepartment.split(":").map(s => s.trim());
    if (selectedParts.length !== 2) return false;
    const [selectedDept, selectedProgram] = selectedParts;

    // Check if any group matches the scanned student's department and program.
    return groups.some(group => {
      if (group.includes(":")) {
        const [dept, programsStr] = group.split(":");
        const programs = programsStr.split(",").map(p => p.trim());
        return dept.trim() === selectedDept && programs.includes(selectedProgram);
      }
      // Fallback: if the group doesn't use colon formatting, check for an exact match.
      return group === props.selectedDepartment;
    });
  });
});


const fetchAnnouncements = async () => {
  try {
    const response = await HTTP.get("/api/announcements");
    const fetchedAnnouncements = response.data.announcements || [];
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

function onMainSwiper(swiper) {
  // If only one announcement, reset immediately using the provided instance
  if (filteredAnnouncements.value.length === 1) {
    setTimeout(() => {
      swiper.autoplay.stop();
      swiper.slideToLoop(0, 300);
      swiper.autoplay.start();
    }, 3000);
  }
}

// Watch filteredAnnouncements to handle the case where there is only one announcement.
watch(filteredAnnouncements, (newVal) => {
  if (newVal.length === 1) {
    setTimeout(() => {
      resetDepartment();
    }, 3000);
  }
});


function onThumbsSwiper(swiper) {
  thumbsSwiper.value = swiper;
}

function onSlideChangeTransitionStart() {
  overlayOpacity.value = 1;
}

function onSlideChangeTransitionEnd(swiper) {
  overlayOpacity.value = 0;
  activeIndex.value = swiper.activeIndex;

  if (
    filteredAnnouncements.value.length > 1 &&
    swiper.activeIndex === filteredAnnouncements.value.length - 1
  ) {
    setTimeout(() => {
      // Stop autoplay to prevent interference
      swiper.autoplay.stop();
      // Use a non-zero duration for a visible transition
      swiper.slideToLoop(0, 300);
      // Restart autoplay if needed
      swiper.autoplay.start();
      resetDepartment();
    }, 1000);
  }
}

// Define resetDepartment to emit the event to the parent.
function resetDepartment() {
  console.log(true);
  emit("update:selectedDepartment", "GENERAL");
}

watch(
  () => props.active,
  (newVal) => {
    if (newVal && props.announcement.type === 'text' && scrollContent.value) {
      // Reset scroll position to top
      scrollContent.value.scrollTop = 0;
      // Remove the animation and force a reflow
      scrollContent.value.style.animation = 'none';
      void scrollContent.value.offsetWidth; // trigger reflow
      // Reapply the animation
      scrollContent.value.style.animation = 'marquee 10s linear infinite';
      marqueeAnimation.value = 'marquee 20s linear infinite';
    }
  }
);


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
