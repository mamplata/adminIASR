<template>
  <div class="h-screen w-full">
    <!-- Loading state while checking registration -->
    <div v-if="checkingRegistration" class="flex items-center justify-center h-full">
      <p>Checking device registration...</p>
    </div>

    <!-- Once registration check is done -->
    <div v-else>
      <!-- Main page view: display full layout if device is registered -->
      <div v-if="isRegistered" class="flex flex-col lg:flex-row h-screen w-full relative">
        <!-- Left Section: DaisyTimeIn with 4/12 width -->
        <div class="w-full lg:w-4/12">
          <DaisyTimeIn :deviceFingerprint="deviceFingerprint" />
        </div>
        <!-- Right Section: AnnouncementsCarousel with 8/12 width -->
        <div class="w-full lg:w-8/12 relative">
          <AnnouncementsCarousel :deviceName="deviceName" />
        </div>
      </div>

      <!-- Registration view: show only when not registered -->
      <div v-else>
        <DeviceRegistration @registered="handleRegistered" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { initializeSocket } from '@/composables/socket';
import HTTP from '@/http';
import AnnouncementsCarousel from "@/components/AnnouncementsCarousel.vue";
import DaisyTimeIn from "@/components/DaisyTimeIn.vue";
import DeviceRegistration from "@/components/DeviceRegistration.vue";

const isRegistered = ref(false);
const deviceName = ref('');
const deviceFingerprint = ref('');
const checkingRegistration = ref(true);

onMounted(() => {
  checkRegistration();
});

async function checkRegistration() {
  try {
    const response = await HTTP.get('/api/device/status', { withCredentials: true });
    if (response.data) {
      if (response.data.device_name) {
        deviceName.value = response.data.device_name;
      }
      if (response.data.device_fingerprint) {
        deviceFingerprint.value = response.data.device_fingerprint;
        initializeSocket(deviceFingerprint.value);
      }
      isRegistered.value = true;
    }
  } catch (error) {
    isRegistered.value = false;
  } finally {
    checkingRegistration.value = false;
  }
}

function handleRegistered(payload) {
  deviceName.value = payload.deviceName;
  if (payload.deviceFingerprint) {
    deviceFingerprint.value = payload.deviceFingerprint;
    initializeSocket(payload.deviceFingerprint);
  }
  isRegistered.value = true;
}
</script>
