<!-- RFIDScanner.vue -->
<template>
  <div class="h-screen w-full">
    <!-- Loading state while checking registration -->
    <div v-if="deviceStore.checkingRegistration" class="flex items-center justify-center h-full">
      <p>Checking device registration...</p>
    </div>

    <!-- Once registration check is done -->
    <div v-else>
      <!-- Main page view: display full layout if device is registered -->
      <div v-if="deviceStore.isRegistered" class="flex flex-col lg:flex-row h-screen w-full relative">
        <!-- Left Section: DaisyTimeIn with 4/12 width -->
        <div class="w-full lg:w-5/12">
          <DaisyTimeIn />
        </div>
        <!-- Right Section: AnnouncementsCarousel with 8/12 width -->
        <div class="w-full lg:w-7/12 relative">
          <AnnouncementsCarousel />
        </div>
      </div>

      <!-- Registration view: show only when not registered -->
      <div v-else>
        <DeviceRegistration />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useDeviceStore } from '@/stores/deviceStore';
import AnnouncementsCarousel from "@/components/AnnouncementsCarousel.vue";
import DaisyTimeIn from "@/components/DaisyTimeIn.vue";
import DeviceRegistration from "@/components/DeviceRegistration.vue";

const deviceStore = useDeviceStore();

onMounted(() => {
  deviceStore.checkRegistration();
});
</script>
