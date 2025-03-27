<template>
    <div class="relative h-full texture-bg">
        <!-- Header positioned in the top left corner -->
        <header class="absolute top-0 left-0 m-4 z-50">
            <img :src="ITAPLOGO" alt="iASR Logo" class="w-32" />
        </header>

        <!-- Main content container -->
        <div class="flex flex-col items-center justify-center h-full mx-10 pt-10">
            <transition name="fade" mode="out-in">
                <div
                    :key="timeInStore.isLoading ? 'loading' : (timeInStore.scannedStudent || timeInStore.nfcError) ? 'card' : 'prompt'">
                    <template v-if="timeInStore.isLoading">
                        <!-- Loading Spinner -->
                        <div class="flex flex-col items-center justify-center mt-12">
                            <i class="fas fa-spinner fa-spin text-6xl text-green-700"></i>
                            <h1 class="text-2xl mt-4">Loading...</h1>
                        </div>
                    </template>

                    <template v-else-if="timeInStore.scannedStudent || timeInStore.nfcError">
                        <template v-if="timeInStore.nfcError">
                            <div v-if="timeInStore.nfcError === 'Unauthorized access.'"
                                class="card card-side bg-error text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center h-56">
                                <figure class="z-10 flex-shrink-0 px-4">
                                    <i class="fas fa-exclamation-triangle ml-2 text-4xl"></i>
                                </figure>
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Unauthorized Access</h2>
                                    <p class="text-lg">You do not have permission to access this school.</p>
                                </div>
                            </div>
                            <div v-else-if="timeInStore.nfcError === 'Card is not activated'"
                                class="card card-side bg-warning text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center my-4 h-56">
                                <figure class="z-10 flex-shrink-0 px-4">
                                    <i class="fas fa-info-circle ml-2 text-5xl"></i>
                                </figure>
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Card Activation Expired</h2>
                                    <p class="text-lg">
                                        Your card activation period has expired. Please contact support for further
                                        assistance (MIS Department).
                                    </p>
                                </div>
                            </div>
                            <div v-else
                                class="card card-side bg-error text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center h-56">
                                <figure class="z-10 flex-shrink-0 px-4">
                                    <i class="fas fa-exclamation-triangle ml-2 text-4xl"></i>
                                </figure>
                                <div class="card-body">
                                    <h2 class="card-title text-2xl">Error</h2>
                                    <p class="text-lg">Error, please try again</p>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div
                                class="bg-gradient-to-l from-slate-300 to-slate-100 text-slate-600 border border-slate-300 max-w-3xl mx-auto rounded-lg overflow-hidden shadow-2xl transform transition">
                                <!-- Header Section with Solid Background Color #198754 -->
                                <div class="bg-[#198754] p-6">
                                    <div class="flex flex-col md:flex-row items-center">
                                        <!-- Student Image -->
                                        <div class="flex-shrink-0">
                                            <img v-if="timeInStore.scannedStudent.image"
                                                :src="timeInStore.scannedStudent.image" alt="Student Photo"
                                                class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-full border-4 border-white" />
                                        </div>
                                        <!-- Student Details -->
                                        <div class="mt-4 md:mt-0 md:ml-6 text-center md:text-left text-white">
                                            <h2 class="text-2xl font-bold">
                                                {{ timeInStore.scannedStudent.fName }}
                                                {{ timeInStore.scannedStudent.lName }}
                                            </h2>
                                            <p class="text-sm">Program: {{ timeInStore.scannedStudent.program }}</p>
                                            <p class="text-sm">Department: {{ timeInStore.scannedStudent.department }}
                                            </p>
                                            <p class="text-sm">Year Level: {{ timeInStore.scannedStudent.yearLevel }}
                                            </p>
                                            <p class="text-sm">Last Enrolled:
                                                {{ timeInStore.scannedStudent.last_enrolled_at }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Schedule Section -->
                                <div class="bg-white p-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="badge badge-xs badge-primary">Today</span>
                                        <span class="text-lg text-gray-700">
                                            {{ new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) }}
                                        </span>
                                    </div>
                                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">Schedule</h3>
                                    <ul class="space-y-2" v-if="todaySchedule.length">
                                        <li v-for="item in todaySchedule" :key="item.id"
                                            class="flex items-center text-sm text-gray-700">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            <span>
                                                {{ item.courseCode }}: {{ item.courseDescription }} | {{ item.time }} |
                                                {{ item.room }} | Section {{ item.section }}
                                            </span>
                                        </li>
                                    </ul>
                                    <p v-else class="text-center text-gray-600">{{ timeInStore.scheduleError }}</p>
                                </div>
                            </div>
                        </template>
                    </template>

                    <template v-else>
                        <!-- "Tap your card" prompt or scanner status -->
                        <div class="text-center">
                            <img :src="logoRFID" alt="RFID Icon" class="w-40 mx-auto"
                                :class="{ 'animate-zoom-in-out': !timeInStore.scannerStatusLoading && isScanEnabled }" />
                            <h1 class="text-4xl font-bold mt-4"
                                :class="{ 'text-green-900': !timeInStore.scannerStatusLoading && isScanEnabled, 'text-gray-500': !timeInStore.scannerStatusLoading && !isScanEnabled }">
                                {{ timeInStore.scannerStatusLoading ? 'Checking scanner status...' : (isScanEnabled ? 'Tap your card' : 'Scanner Disabled') }}
                            </h1>
                        </div>
                    </template>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useTimeInStore } from '@/stores/timeInStore';
import ITAPLOGO from '@/assets/img/ITAPLOGO.png';
import logoRFID from '@/assets/img/logoRFID.png';

const timeInStore = useTimeInStore();

// Expose the schedule from timeInStore as a computed property.
const todaySchedule = computed(() => timeInStore.schedule);

// Compute if scanning is enabled based on socket connection and the online status of the Time In scanner.
const isScanEnabled = computed(() => {
    if (!timeInStore.socketConnected || timeInStore.scannerStatusLoading) return false;
    return timeInStore.timeInScanner && timeInStore.timeInScanner.online;
});

onMounted(() => {
    // Initialize the socket(s) and sync Time In scanner state via the watcher in timeInStore.
    timeInStore.initializeSocket();
    // After a delay (allowing state sync from scannerPortStore), trigger NFC read.
    setTimeout(() => {
        timeInStore.scannerStatusLoading = false;
        timeInStore.readNfcCard();
    }, 3000);
});
</script>

<style scoped>
.texture-bg {
    background-color: #f6f7fb;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='199' viewBox='0 0 100 199'%3E%3Cg fill='%23198754' fill-opacity='0.35'%3E%3Cpath d='M0 199V0h1v1.99L100 199h-1.12L1 4.22V199H0zM100 2h-.12l-1-2H100v2z'%3E%3C/path%3E%3C/g%3E%3C/svg%3E");
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}
</style>