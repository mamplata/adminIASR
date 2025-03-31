<template>
    <div class="relative h-full texture-bg">
        <!-- Header -->
        <header class="fixed top-[calc(1vh)] left-[calc(1vw)] z-50">
            <img :src="ITAPLOGO" alt="ITAP Logo" class="w-[calc(6vw+6vh)] h-auto" />
        </header>


        <!-- Main content container -->
        <div class="flex flex-col items-center justify-center h-full mx-10 pt-[3vh]">
            <transition name="fade" mode="out-in">
                <div
                    :key="timeScannerStore.isLoading ? 'loading' : (timeScannerStore.scannedStudent || timeScannerStore.nfcError) ? 'card' : 'prompt'">
                    <template v-if="timeScannerStore.isLoading">
                        <!-- Loading Spinner -->
                        <div class="flex flex-col items-center justify-center mt-12">
                            <i class="fas fa-spinner fa-spin text-6xl text-green-700"></i>
                            <h1 class="text-2xl mt-4">Loading...</h1>
                        </div>
                    </template>

                    <template v-else-if="timeScannerStore.scannedStudent || timeScannerStore.nfcError">
                        <template v-if="timeScannerStore.nfcError">
                            <div v-if="timeScannerStore.nfcError === 'Unauthorized access.'"
                                class="card card-side bg-error text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center h-56">
                                <figure class="z-10 flex-shrink-0 px-4">
                                    <i class="fas fa-exclamation-triangle ml-2 text-4xl"></i>
                                </figure>
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Unauthorized Access</h2>
                                    <p class="text-lg">You do not have permission to access this school.</p>
                                </div>
                            </div>
                            <div v-else-if="timeScannerStore.nfcError === 'Card is not activated'"
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
                                            <img v-if="timeScannerStore.scannedStudent.image"
                                                :src="timeScannerStore.scannedStudent.image" alt="Student Photo"
                                                class="w-24 h-24 md:w-32 md:h-32 object-cover rounded-full border-4 border-white" />
                                        </div>
                                        <!-- Student Details -->
                                        <div class="mt-4 md:mt-0 md:ml-6 text-center md:text-left text-white">
                                            <h2 class="text-2xl font-bold">
                                                {{ timeScannerStore.scannedStudent.fName }}
                                                {{ timeScannerStore.scannedStudent.lName }}
                                            </h2>
                                            <p class="text-sm">Program: {{ timeScannerStore.scannedStudent.program }}
                                            </p>
                                            <p class="text-sm">Department:
                                                {{ timeScannerStore.scannedStudent.department }}</p>
                                            <p class="text-sm">Year Level:
                                                {{ timeScannerStore.scannedStudent.yearLevel }}</p>
                                            <p class="text-sm">Last Enrolled:
                                                {{ timeScannerStore.scannedStudent.last_enrolled_at }}</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Schedule Section -->
                                <div class="bg-white p-6">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="badge badge-xs badge-primary">Today</span>
                                        <span class="text-lg text-gray-700">
                                            {{ new Date().toLocaleDateString('en-US', {
                                                month: 'long', day: 'numeric',
                                                year: 'numeric'
                                            }) }}
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
                                    <p v-else class="text-center text-gray-600">{{ timeScannerStore.scheduleError }}</p>
                                </div>
                            </div>
                        </template>
                    </template>
                    <template v-else>
                        <!-- "Tap your card" prompt or scanner status -->
                        <div class="text-center">
                            <img :src="logoRFID" alt="RFID Icon" class="w-[calc(10vw)] mx-auto"
                                :class="{ 'animate-zoom-in-out': !timeScannerStore.scannerStatusLoading && isScanEnabled }" />
                            <h1 class=" font-bold mt-[1vh] text-[calc(1.5vh+1.5vw)]"
                                :class="{ 'text-green-900': !timeScannerStore.scannerStatusLoading && isScanEnabled, 'text-gray-500': !timeScannerStore.scannerStatusLoading && !isScanEnabled }">
                                {{ timeScannerStore.scannerStatusLoading ? 'Checking scanner status...' : (isScanEnabled
                                    ? 'Tap your card' : 'Scanner Disabled') }}
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
import { useTimeScannerStore } from '@/stores/timeScannerStore';
import ITAPLOGO from '@/assets/img/ITAPLOGO.png';
import logoRFID from '@/assets/img/logoRFID.png';

const timeScannerStore = useTimeScannerStore();

// Expose the schedule as a computed property.
const todaySchedule = computed(() => timeScannerStore.schedule);

// Compute if scanning is enabled (using Time In scanner for UI purposes).
const isScanEnabled = computed(() => {
    if (!timeScannerStore.socketConnected || timeScannerStore.scannerStatusLoading) return false;
    return timeScannerStore.timeInScanner && timeScannerStore.timeInScanner.online;
});

onMounted(() => {
    // Initialize the combined time scanner store.
    timeScannerStore.initializeSocket();
});
</script>

<style scoped>
.texture-bg {
    width: calc(1vw +1vh);
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