<template>
  <header class="header-container">
    <div class="header-content">
      <div class="logo-container">
        <img :src="pncLogo" alt="PNC Logo" class="pnc-logo">
      </div>
      <div class="title-container">
        <img :src="logoIASR" alt="iASR Logo" class="logo-iasr">
        <span class="text">{{ title }}</span>
      </div>
    </div>
  </header>
  <hr>
</template>
  
 
<script>
import logoIASR from "@/assets/img/logoIASR.png";
import pncLogo from "@/assets/img/pnc-logo-1.png";

export default {
    name: "Header",
    data() {
        return {
            title: "Intelligent Attendance System with RFID",
            logoIASR,
            pncLogo,
        }
    },
}
</script>

<style scope>
.header-container {
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  width: 100%;
  height: 110px;
  background-color: transparent;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  width: 100%;
  padding: 10px 50px;
}

.logo-container {
  display: flex;
  align-items: center;
  flex: 0;
}

.pnc-logo {
  height: 100px;
  width: auto;
}

.title-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  flex: 2;
}

.logo-iasr {
  width: 90px;
  height: auto;
}

.text {
  font-size: 2rem;
  font-weight: bold;
  color: #198754;
  margin-top: 5px;
}
</style>