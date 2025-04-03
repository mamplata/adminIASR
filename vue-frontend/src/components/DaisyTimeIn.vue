<template>
    <div class="relative h-full texture-bg">
        <!-- Header -->
        <header class="fixed top-[calc(1vh)] left-[calc(1vw)] z-50">
            <img :src="ITAPLOGO" alt="ITAP Logo" class="w-[calc(6vw+6vh)] h-auto" />
        </header>


        <!-- Main content container -->
        <div class="flex flex-col items-center justify-center h-full mx-[3vw] pt-[3vh]">
            <transition name="fade" mode="out-in">
                <div
                    :key="timeScannerStore.isLoading ? 'loading' : (timeScannerStore.scannedStudent || timeScannerStore.nfcError) ? 'card' : 'prompt'">
                    <template v-if="timeScannerStore.isLoading">
                        <!-- Loading Spinner -->
                        <div class="flex flex-col items-center justify-center mt-[5vh]">
                            <i class="fas fa-spinner fa-spin text-[calc(3vw+3vh)] text-green-700"></i>
                            <h1 class="text-[calc(1.5vw+1.5vh)] mt-[1vh]">Loading...</h1>
                        </div>
                    </template>

                    <template v-else-if="timeScannerStore.scannedStudent || timeScannerStore.nfcError">
                        <template v-if="timeScannerStore.nfcError">
                            <div v-if="timeScannerStore.nfcError === 'Unauthorized access.'"
                                class="card card-side bg-error rounded-[calc(0.5vw+0.5vh)] text-white shadow-[calc(3vw+3vh)] hover:shadow-[calc(3.5vw+3.5vh)] transition-shadow duration-300 relative overflow-hidden w-full max-w-screen card-3d flex flex-col md:flex-row items-center justify-center h-[40vh]">
                                <figure class="z-10 flex-shrink-0 px-[1vh]">
                                    <i class="fas fa-exclamation-triangle ml-[0.5vw] text-[calc(3vw+3vh)]"></i>
                                </figure>
                                <div class="card-body z-10">
                                    <h2 class="card-title text-[calc(1.2vw+1.2vh)]">Unauthorized Access</h2>
                                    <p class="text-[calc(1vw+1vh)]">You do not have permission to access this
                                        school.</p>
                                </div>
                            </div>
                            <div v-else-if="timeScannerStore.nfcError === 'Card is not activated'"
                                class="card card-side bg-warning rounded-[calc(0.5vw+0.5vh)] text-white shadow-[calc(3vw+3vh)] hover:shadow-[calc(3.5vw+3.5vh)] transition-shadow duration-300 relative overflow-hidden w-full max-w-screen card-3d flex flex-col md:flex-row items-center justify-center my-[2vh] h-[40vh]">
                                <figure class="z-10 flex-shrink-0 px-[1vh]">
                                    <i class="fas fa-info-circle ml-[0.5vw] text-[calc(3vw+3vh)]"></i>
                                </figure>
                                <div class="card-body z-10">
                                    <h2 class="card-title text-[calc(1.2vw+1.2vh)]">Card Activation Expired</h2>
                                    <p class="text-[calc(1vw+1vh)]">
                                        Your card activation period has expired. Please contact support for further
                                        assistance (MIS Department).
                                    </p>
                                </div>
                            </div>
                            <div v-else
                                class="card card-side bg-error rounded-[calc(0.5vw+0.5vh)] text-white shadow-[calc(3vw+3vh)] hover:shadow-[calc(3.5vw+3.5vh)] transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center h-[40vh]">
                                <figure class="z-10 flex-shrink-0 px-[1vh]">
                                    <i class="fas fa-exclamation-triangle ml-[0.5vw] text-[calc(3vw+3vh)]"></i>
                                </figure>
                                <div class="card-body">
                                    <h2 class="card-title text-[calc(1.2vw+1.2vh)]">Error</h2>
                                    <p class="text-[calc(1vw+1vh)]">Error, please try again</p>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div
                                class="bg-gradient-to-l from-slate-300 to-slate-100 text-slate-600 border border-slate-300 max-w-screen mx-auto rounded-[calc(0.5vw+0.5vh)] overflow-hidden shadow-[calc(1vw+1vh)] transform transition">
                                <!-- Header Section with Solid Background Color #198754 -->
                                <div class="bg-[#198754] p-[3vh]">
                                    <div class="flex flex-col md:flex-row items-center">
                                        <!-- Student Image -->
                                        <div class="flex-shrink-0">
                                            <img v-if="timeScannerStore.scannedStudent.image"
                                                :src="timeScannerStore.scannedStudent.image" alt="Student Photo"
                                                class="w-[10vw] h-[10vw] md:w-[12vw] md:h-[12vw] object-cover rounded-full border-[calc(0.2vw+0.2vh)] border-white" />
                                        </div>
                                        <!-- Student Details -->
                                        <div class="mt-[2vh] md:mt-0 md:ml-[1vw] text-center md:text-left text-white">
                                            <h2 class="text-[calc(1.2vw+1.2vh)] font-bold">
                                                {{ timeScannerStore.scannedStudent.fName }}
                                                {{ timeScannerStore.scannedStudent.lName }}
                                            </h2>
                                            <p class="text-[calc(0.8vw+0.8vh)]">Program: {{
                                                timeScannerStore.scannedStudent.program }}
                                            </p>
                                            <p class="text-[calc(0.8vw+0.8vh)]">Department:
                                                {{ timeScannerStore.scannedStudent.department }}</p>
                                            <p class="text-[calc(0.8vw+0.8vh)]">Year Level:
                                                {{ timeScannerStore.scannedStudent.yearLevel }}</p>
                                            <p class="text-[calc(0.8vw+0.8vh)]">Last Enrolled:
                                                {{ timeScannerStore.scannedStudent.last_enrolled_at }}</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Schedule Section -->
                                <div class="bg-white p-[calc(1.5vw+1.5vh)]">
                                    <div class="flex justify-between items-center mb-[1vh]">
                                        <span
                                            class="badge h-[5vh] rounded-[calc(1vw+1vh)] text-[calc(1vw+1vh)] badge-primary">Today</span>
                                        <span class="text-[calc(1vw+1vh)] text-gray-700">
                                            {{ new Date().toLocaleDateString('en-US', {
                                                month: 'long', day: 'numeric',
                                                year: 'numeric'
                                            }) }}
                                        </span>
                                    </div>
                                    <h3 class="text-[calc(1.2vw+1.2vh)] font-semibold text-gray-800 mb-[1vh]">Schedule
                                    </h3>
                                    <ul class="space-y-[0.5vw]" v-if="todaySchedule.length">
                                        <li v-for="item in todaySchedule" :key="item.id"
                                            class="flex items-center text-sm text-gray-700">
                                            <i class="fas fa-check-circle text-green-500 mr-[05.vw]"></i>
                                            <span class="text-[calc(0.8vw+0.8vh)]">
                                                {{ item.courseCode }}: {{ item.courseDescription }} | {{ item.time }} |
                                                {{ item.room }} | Section {{ item.section }}
                                            </span>
                                        </li>
                                    </ul>
                                    <p v-else class="text-center text-[calc(0.8vw+0.8vh)] text-gray-600">{{
                                        timeScannerStore.scheduleError }}</p>
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