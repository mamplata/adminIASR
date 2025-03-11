<template>
  <div class="flex flex-col lg:flex-row h-screen w-full">
    <div class="w-full lg:w-2/5 flex flex-col items-center justify-center bg-white p-8">
      <div>
        <img :src="iASRPNC" alt="iASR Logo" class="w-48">
      </div>
      <div class="text-center mt-12">
        <img :src="logoRFID" alt="RFID Icon" class="w-32 mx-auto animate-bounce">
        <h1 class="text-4xl font-bold text-green-600 mt-4">Tap your card</h1>
      </div>
    </div>

    <div class="w-full lg:w-3/5 relative">
      <carousel :items-to-show="1" :wrap-around="true" :autoplay="3000" class="w-full h-full">
        <slide v-for="(announcement, index) in announcements" :key="index" class="w-full h-full">
          <div class="w-full h-full flex justify-center items-center">
            <img :src="announcement.img" alt="Announcement" class="w-full h-full object-cover">
          </div>
        </slide>
      </carousel>
    </div>
  </div>
</template>

<script>
import iASRPNC from "@/assets/img/iASRPNC.png";
import logoRFID from "@/assets/img/logoRFID.png";
import announce1 from "@/assets/img/announce1.jpg";
import announce2 from "@/assets/img/announce2.jpg";
import { Carousel, Slide } from "vue3-carousel";
import "vue3-carousel/dist/carousel.css";

export default {
  components: {
    Carousel,
    Slide,
  },
  data() {
    return {
      iASRPNC,
      logoRFID,
      announcements: [
        { img: announce1 },
        { img: announce2 }
      ]
    };
  }
};
</script>

<style scoped>
/* Ensure full-bleed effect */
.carousel {
  width: 100%;
  height: 100%;
}
</style>
