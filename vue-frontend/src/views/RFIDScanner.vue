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
        <div class="w-full lg:w-4/12">
          <DaisyTimeIn @scannedStudent="handleScannedStudent" @loading="handleDaisyLoading"
            :deviceFingerprint="deviceStore.deviceFingerprint" />
        </div>
        <!-- Right Section: AnnouncementsCarousel with 8/12 width -->
        <div class="w-full lg:w-8/12 relative">
          <AnnouncementsCarousel v-model:selectedDepartment="selectedDepartment" :deviceName="deviceStore.deviceName"
            :loading="daisyLoading" :key="departmentKey" @filterMatch="handleFilterMatch" />
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

const selectedDepartment = ref('GENERAL');
const departmentKey = ref(Date.now());
const daisyLoading = ref(false);

onMounted(() => {
  deviceStore.checkRegistration();
});
let match;

function handleScannedStudent(student) {
  if (!student || !student.department) return;
  if (selectedDepartment.value !== student.department + ": " + student.program) {
    selectedDepartment.value = student.department + ": " + student.program;
    console.log(selectedDepartment.value);
    // Only force a remount if the new filter actually yields results.
    if (match) {
      departmentKey.value = Date.now();
    }
  }
}

function handleFilterMatch(match) {
  match = match;
}

// This handler will receive true/false from DaisyTimeIn when it starts/finishes loading
function handleDaisyLoading(isLoading) {
  daisyLoading.value = isLoading;
}

</script>
