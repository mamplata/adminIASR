<template>
  <div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Left Section -->
    <div class="w-full lg:w-2/5 flex flex-col items-center justify-center">
      <img :src="iASRPNC" alt="iASR Logo" class="w-48">
      <div v-if="!cardTapped" class="text-center mt-12">
        <img :src="logoRFID" alt="RFID Icon" class="w-32 mx-auto animate-zoom-in-out">
        <h1 class="text-4xl font-bold text-green-900 mt-4">Tap your card</h1>
      </div>

      <div v-else class="text-center mt-12">
        <!-- Card ID -->
        <div class="border border-black rounded-lg p-4 text-center text-lg font-semibold w-64 mb-20">
          Card ID
        </div>

        <!-- Schedule -->
        <div class="border border-black rounded-lg p-4 text-center text-lg font-semibold w-64">
          Schedule
        </div>
      </div>

      <button @click="tapCard" class="mt-4 px-4 py-2 bg-green-600 text-white rounded">
        Simulate Card Tap
      </button>
    </div>


    <!-- Right Section (Custom Carousel) -->
    <div class="w-full lg:w-3/5 relative overflow-hidden flex justify-center items-center"
      :style="{ backgroundImage: `url(${bgAnnounce})`, backgroundSize: 'cover', backgroundPosition: 'center' }">
      <div class="w-full h-[400px] lg:h-[500px] overflow-hidden relative">
        <!-- Carousel Container -->
        <div class="flex w-full h-full" :style="containerStyle">
          <!-- Slides -->
          <div v-for="(announcement, index) in slides" :key="index" class="flex-none w-full h-full">
            <img v-if="announcement.content.file_path && announcement.type == 'image'"
              :src="`${apiUrl}${announcement.content.file_path}`" alt="Announcement" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import HTTP from "@/http"; // Import Axios instance
import iASRPNC from "@/assets/img/iASRPNC.png";
import logoRFID from "@/assets/img/logoRFID.png";
import bgAnnounce from "@/assets/img/bgAnnounce.png";

const cardTapped = ref(false);
const announcements = ref([]);
const currentIndex = ref(0);
const disableTransition = ref(false);
let timer = null;
const apiUrl = import.meta.env.VITE_API_URL;

// Simulate card tap event
const tapCard = () => {
  cardTapped.value = !cardTapped.value;
};

// Fetch announcements from API
const fetchAnnouncements = async () => {
  try {
    const response = await HTTP.get("/api/announcements");
    announcements.value = response.data.announcements;
  } catch (error) {
    console.error("Error fetching announcements:", error);
  }
};

const slides = computed(() => {
  return announcements.value.length > 0
    ? [...announcements.value, announcements.value[0]]
    : [];
});


// Compute transform styles for sliding effect
const containerStyle = computed(() => ({
  transform: `translateX(-${currentIndex.value * 100}%)`,
  transition: disableTransition.value ? "none" : "transform 0.5s ease-in-out",
}));

// Auto-slide every 3 seconds
onMounted(async () => {
  await fetchAnnouncements();

  timer = setInterval(() => {
    currentIndex.value++;

    if (currentIndex.value >= announcements.value.length) {
      setTimeout(() => {
        disableTransition.value = true;
        currentIndex.value = 0;
        setTimeout(() => disableTransition.value = false, 50);
      }, 500);
    }
  }, 3000);
});

// Clear interval when component is unmounted
onUnmounted(() => clearInterval(timer));
</script>
