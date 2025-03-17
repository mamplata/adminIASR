<!-- AnnouncementsCarousel.vue -->
<template>
  <div class="w-full h-full relative overflow-hidden" :style="{
    '--header-height': '6rem',
    backgroundImage: `url(${bgAnnounce})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }">
    <!-- Header with Announcements title and Port Info Button -->
    <div class="absolute top-0 left-0 right-0 z-10">
      <div class="bg-[#198754] w-full px-6 flex items-center relative" :style="{ height: 'var(--header-height)' }">
        <h1 class="text-white text-5xl font-bold mx-auto">Announcements</h1>
        <button @click="openPortStatusModal"
          class="absolute right-6 top-1/2 transform -translate-y-1/2 bg-blue-800 text-white p-3 rounded-full flex items-center justify-center">
          <i class="fas fa-info-circle text-2xl"></i>
        </button>
      </div>
    </div>

    <!-- Gallery Section (pushed down to account for header height) -->
    <div class="w-full flex justify-center items-center"
      :style="{ marginTop: 'var(--header-height)', height: 'calc(100% - var(--header-height))' }">
      <transition name="fade" mode="out-in">
        <div :key="currentIndex" :class="gridClasses">
          <div v-for="(announcement, index) in visibleAnnouncements" :key="announcement.id || index"
            :class="['bg-white shadow p-2', getCardCornerClasses()]">
            <DaisyCardAnnouncement :announcement="announcement" />
          </div>
        </div>
      </transition>
    </div>

    <!-- Port Status Modal -->
    <PortStatus v-if="showPortStatusModal" :deviceName="deviceName" :timeInInfo="timeInInfo" :timeOutInfo="timeOutInfo"
      :newScannerInfo="newScannerInfo" @close="closePortStatusModal" @assignRole="assignRole" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import HTTP from '@/http';
import bgAnnounce from '@/assets/img/bgAnnounce.png';
import DaisyCardAnnouncement from '@/components/Daisy/DaisyCardAnnouncement.vue';
import PortStatus from '@/components/PortStatus.vue';

// Announcement state
const announcements = ref([]);
const currentIndex = ref(0);
let timer = null;

// Fetch announcements from the API
const fetchAnnouncements = async () => {
  try {
    const response = await HTTP.get('/api/announcements');
    announcements.value = response.data.announcements;
  } catch (error) {
    console.error('Error fetching announcements:', error);
  }
};

// Compute visible announcements (4 per page)
const visibleAnnouncements = computed(() => {
  const start = currentIndex.value * 4;
  return announcements.value.slice(start, start + 4);
});

// Compute grid container classes
const gridClasses = computed(() => {
  let classes = ['w-full', 'h-full', 'p-4', 'grid', 'gap-4'];
  const count = visibleAnnouncements.value.length;
  // For a single item, center it; otherwise, use two columns
  if (count === 1) {
    classes.push('grid-cols-1', 'justify-items-center', 'items-center');
  } else {
    classes.push('grid-cols-2');
  }
  return classes.join(' ');
});

// Function to determine corner classes for each card based on its position
const getCardCornerClasses = () => {
  return 'rounded-lg';
};

// Cycle to the next gallery page
const nextGallery = () => {
  currentIndex.value = (currentIndex.value + 1) % Math.ceil(announcements.value.length / 4);
};

// Port status modal state and functions
const showPortStatusModal = ref(false);
const timeInInfo = ref(null);
const timeOutInfo = ref(null);
const newScannerInfo = ref({ uniqueKey: '', portPath: '' });
const deviceName = ref('');

function openPortStatusModal() {
  showPortStatusModal.value = true;
}

function closePortStatusModal() {
  showPortStatusModal.value = false;
}

function assignRole(role) {
  // Implement role assignment via socket if needed
}

onMounted(async () => {
  await fetchAnnouncements();
  timer = setInterval(nextGallery, 5000);
});

onUnmounted(() => clearInterval(timer));
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.8s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
