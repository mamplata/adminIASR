<template>
  <div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Left Section -->
    <div class="w-full lg:w-2/5">
      <DaisyTimeIn />
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
import HTTP from "@/http"; // Axios instance
import bgAnnounce from "@/assets/img/bgAnnounce.png";
import DaisyCardAnnouncement from "@/components/DaisyCardAnnouncement.vue";
import DaisyTimeIn from "@/components/DaisyTimeIn.vue"; // New left section component

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
  announcements.value.length > 0 ? [...announcements.value, announcements.value[0]] : []
);

const containerStyle = computed(() => ({
  transform: `translateX(-${currentIndex.value * 100}%)`,
  transition: disableTransition.value ? "none" : "transform 0.5s ease-in-out",
}));

onMounted(async () => {
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
});

onUnmounted(() => clearInterval(timer));
</script>
