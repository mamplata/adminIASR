<template>
  <header class="bg-base-100 shadow-md flex items-center justify-between px-6 py-4">
    <div class="flex items-center space-x-4">
      <img :src="pncLogo" alt="PNC Logo" class="w-16">
      <div class="flex flex-col">
        <img :src="logoIASR" alt="iASR Logo" class="w-20">
        <span class="text-2xl font-bold text-green-600">{{ title }}</span>
      </div>
    </div>
  </header>
  <hr />
</template>

<script setup>
import logoIASR from "@/assets/img/logoIASR.png";
import pncLogo from "@/assets/img/pnc-logo-1.png";

const title = "Intelligent Attendance System with RFID";
</script>
