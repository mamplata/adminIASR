<template>
    <div class="container-fluid">
      <div class="left-section">
        <div class="logo">
          <img :src="iASRPNC" alt="iASR Logo" />
        </div>
        <div class="tap-card">
          <img :src="logoRFID" alt="RFID Icon" />
          <h1 class="text">Tap your card</h1>
        </div>
      </div>
      <div class="right-section" :style="{ backgroundImage: `url(${bgAnnounce})` }">
        <h2 class="announcements-title">Announcements</h2>
        <div class="announcements-box">
          <Carousel ref="carousel" v-bind="carouselConfig">
            <Slide v-for="(announcement, index) in announcements" :key="index">
              <div class="announcement">
                <div class="image-wrapper">
                  <img :src="announcement.img" alt="Announcement" />
                </div>
              </div>
            </Slide>
          </Carousel>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import iASRPNC from "@/assets/img/iASRPNC.png";
  import logoRFID from "@/assets/img/logoRFID.png";
  import bgAnnounce from "@/assets/img/bgAnnounce.png";
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
        bgAnnounce,
        announcements: [
          { img: announce1 },
          { img: announce2 }
        ],
        carouselConfig: {
          itemsToShow: 1,
          wrapAround: true,
          autoplay: 3000,
        }
      };
    },
    methods: {

    },
  };
  </script>
  
  <style scoped>
  .container-fluid {
    display: flex;
    width: 100%;
    min-height: 100vh;
    background: white;
    padding: 0;
  }
  
  .left-section {
    width: 40%;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    padding: 20px;
  }
  
  .logo img {
    max-width: 250px;
    margin-bottom: 20px;
  }
  
  .tap-card {
    margin-top: 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  
  .tap-card img {
    max-width: 300px;
  }
  
  .text {
    font-size: 48px;
    font-weight: bold;
    color: #198754;
    margin-top: 60px;
    animation: zoomInOut 2s ease-in-out infinite;
  }
  
  @keyframes zoomInOut {
    0%, 100% {
      transform: scale(1);
    }
    50% { 
      transform: scale(1.2);
    }
  }
  
  .right-section {
    width: 60%;
    background-size: cover;
    background-position: center;
    padding: 20px;
    position: relative;
  }
  
  .announcements-title {
    color: white;
    font-size: 50px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
  }
  
  .announcements-box {
    width: 95%;
    margin: 0 auto;
    background-color: #333;
  }
  
  .announcement {
    text-align: center;
  }
  
  .image-wrapper {
    height: 100%; 
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  .image-wrapper img {
    width: 100%;
    height: 500px;
    object-fit: cover;
    object-position: center;
  }
  
</style>