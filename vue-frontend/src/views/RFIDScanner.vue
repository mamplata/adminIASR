<template>
  <div class="flex flex-col lg:flex-row h-screen w-full">
    <!-- Left Section -->
    <div class="w-full lg:w-2/5">
      <DaisyTimeIn />
    </div>

    <!-- Right Section (Custom Carousel) -->
    <AnnouncementsCarousel />
  </div>
</template>

<script setup>
import AnnouncementsCarousel from "@/components/AnnouncementsCarousel.vue";
import DaisyTimeIn from "@/components/DaisyTimeIn.vue";
</script>

// add here the device register checking and port management