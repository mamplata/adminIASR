<template>
    <div class="relative h-full texture-bg">
        <!-- Header positioned in the top left corner -->
        <header class="absolute top-0 left-0 m-4">
            <img :src="ITAPLOGO" alt="iASR Logo" class="w-48" />
        </header>

        <!-- Main content container -->
        <div class="flex flex-col items-center justify-center h-full mx-10">
            <!-- Single transition wrapping different states with keyed container -->
            <transition name="fade" mode="out-in">
                <div :key="isLoading ? 'loading' : (scannedStudent || nfcError) ? 'card' : 'prompt'">
                    <template v-if="isLoading">
                        <!-- Loading Spinner -->
                        <div class="flex flex-col items-center justify-center mt-12">
                            <i class="fas fa-spinner fa-spin text-6xl text-green-700"></i>
                            <h1 class="text-2xl mt-4">Loading...</h1>
                        </div>
                    </template>

                    <template v-else-if="scannedStudent || nfcError">
                        <!-- Error State -->
                        <template v-if="nfcError">
                            <!-- Error Card: Unauthorized Access with 3D effect -->
                            <div v-if="nfcError == 'Unauthorized access.'"
                                class="card card-side bg-error text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center h-64">

                                <!-- Icon Section -->
                                <figure class="z-10 flex-shrink-0 px-4">
                                    <i class="fas fa-exclamation-triangle ml-2 text-4xl"></i>
                                </figure>
                                <!-- Message Section -->
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Unauthorized Access</h2>
                                    <p class="text-lg">
                                        You do not have permission to access this school.
                                    </p>
                                </div>
                            </div>

                            <!-- Error Card: Card Activation Expired with 3D effect -->
                            <div v-if="nfcError == 'Card is not activated'"
                                class="card card-side bg-warning text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center my-4 h-64">
                                <!-- Icon Section -->
                                <figure class="z-10 flex-shrink-0 px-4">
                                    <i class="fas fa-info-circle ml-2 text-5xl"></i>
                                </figure>
                                <!-- Message Section -->
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Card Activation Expired</h2>
                                    <p class="text-lg">
                                        Your card activation period has expired. Please contact support for further
                                        assistance (MIS Department).
                                    </p>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <!-- Student Card Display -->
                            <div
                                class="card p-6 bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-3xl card-3d flex flex-col md:flex-row">

                                <!-- Image Section -->
                                <figure class="z-10">
                                    <img v-if="scannedStudent.image" :src="scannedStudent.image" alt="Student Photo"
                                        class="w-48 md:w-48 object-cover" />
                                </figure>

                                <!-- Details Section -->
                                <div class="card-body z-10">
                                    <h2 class="card-title">{{ scannedStudent.fName }} {{ scannedStudent.lName }}</h2>
                                    <p class="text-sm">Program: {{ scannedStudent.program }}</p>
                                    <p class="text-sm">Department: {{ scannedStudent.department }}</p>
                                    <p class="text-sm">Year Level: {{ scannedStudent.yearLevel }}</p>
                                    <p class="text-sm">Last Enrolled: {{ scannedStudent.last_enrolled_at }}</p>
                                </div>
                            </div>

                            <!-- Weekly Schedule Card with matching 3D effect -->
                            <div
                                class="card p-6 bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-3xl card-3d mt-8">
                                <div class="card-body">
                                    <!-- Header with badge and current date -->
                                    <span class="badge badge-xs badge-primary">Today</span>
                                    <div class="flex justify-between">
                                        <h2 class="text-3xl font-bold">Schedule</h2>
                                        <span class="text-xl">
                                            {{ new Date().toLocaleDateString('en-US', {
                                                month: 'long', day: 'numeric',
                                                year: 'numeric'
                                            }) }}
                                        </span>
                                    </div>
                                    <!-- Schedule Items: only show items scheduled for Wednesday -->
                                    <ul class="mt-6 flex flex-col gap-2 text-xs" v-if="todaySchedule.length">
                                        <li v-for="item in todaySchedule" :key="item.id" class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 me-2"></i>
                                            <span>
                                                {{ item.courseCode }}: {{ item.courseDescription }} |
                                                {{ item.time }} | {{ item.room }} | Section {{ item.section }}
                                            </span>
                                        </li>
                                    </ul>
                                    <p v-else class="mt-6 text-center text-lg">{{ scheduleError }}</p>
                                </div>
                            </div>
                        </template>
                    </template>
                    <template v-else>
                        <!-- "Tap your card" prompt -->
                        <div class="text-center mt-12">
                            <img :src="logoRFID" alt="RFID Icon" class="w-40 mx-auto animate-zoom-in-out" />
                            <h1 class="text-4xl font-bold text-green-900 mt-4">Tap your card</h1>
                        </div>
                    </template>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { getSocket } from '@/composables/socket';
import ITAPLOGO from '@/assets/img/ITAPLOGO.png';
import logoRFID from '@/assets/img/logoRFID.png';
import HTTP from '@/http'; // adjust the path to your axios instance

const scannedStudent = ref(null);
const schedule = ref([]);
const scheduleError = ref(null);
const isReadingNfc = ref(false);
const isLoading = ref(false); // New loading state
const nfcData = ref(null);
const nfcError = ref('');

// Accept deviceFingerprint as a prop (if needed for display or other logic)
const props = defineProps({
    deviceFingerprint: String
});

// Define the custom event to emit the scanned student data to the parent
const emit = defineEmits(['scannedStudent', 'loading']);

let socket = null;

/**
 * Computed property to filter the schedule for items with day "Wednesday"
 */
const todaySchedule = computed(() => {
    return schedule.value.filter(item => item.day === 'Wednesday');
});

/**
 * Initiates the NFC card reading process.
 */
function readNfcCard() {
    if (isReadingNfc.value) return;
    isReadingNfc.value = true;
    nfcData.value = null;
    console.log("📡 Requesting to read NFC card...");
    if (socket) {
        socket.emit('readCard');
        socket.emit('getStoredAssignments');
    } else {
        console.error('Socket connection not established');
    }
}

async function processScannedCard(card) {
    // Show spinner while fetching data and notify the parent
    isLoading.value = true;
    emit('loading', true);
    try {
        const response = await HTTP.post(
            '/api/card/scan',
            { uid: card.uid, data: card.data },
            { withCredentials: true }
        );
        const studentData = response.data.student;
        const scheduleResponse = await HTTP.get(`/api/fetch-schedule/${studentData.studentId}`);
        if (scheduleResponse.data.schedule && scheduleResponse.data.schedule.length > 0) {
            schedule.value = scheduleResponse.data.schedule;
        } else {
            schedule.value = [];
            scheduleError.value = scheduleResponse.data.message || "No schedule available.";
        }
        scannedStudent.value = studentData;
        emit('scannedStudent', scannedStudent.value);
        // Data is ready so turn off the spinner
        isLoading.value = false;
        emit('loading', false);
        setTimeout(() => {
            scannedStudent.value = null;
            schedule.value = [];
            scheduleError.value = "";
            readNfcCard();
        }, 3000);
    } catch (err) {
        isLoading.value = false;
        emit('loading', false);
        nfcError.value = err.response?.data?.error || 'An error occurred during card scan.';
        scannedStudent.value = null;
        schedule.value = [];
        scheduleError.value = "";
        setTimeout(() => {
            nfcError.value = '';
            readNfcCard();
        }, 3000);
    } finally {
        isReadingNfc.value = false;
    }
}

/**
 * Sets up socket listeners to handle NFC scan events.
 */
function setupSocketListeners() {
    if (!socket) return;

    socket.on('connect', () => {
        console.log("Socket connected");
    });

    socket.on('cardRead', (data) => {
        nfcData.value = data;
        processScannedCard(data);
    });

    socket.on('readFailed', (data) => {
        console.error("❌ NFC Read Failed:", data);
        nfcError.value = "Unauthorized access.";
        isReadingNfc.value = false;
        scannedStudent.value = null;
        schedule.value = [];
        setTimeout(() => {
            nfcError.value = '';
            readNfcCard();
        }, 3000);
    });
}

onMounted(() => {
    socket = getSocket();
    if (!socket) {
        console.error('Socket connection is not initialized. Make sure to call initializeSocket() in a parent or registration component.');
        return;
    }
    setupSocketListeners();
    readNfcCard();
});
</script>


<style scoped>
/* Background texture style */
.texture-bg {
    background-color: #f6f7fb;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='199' viewBox='0 0 100 199'%3E%3Cg fill='%23198754' fill-opacity='0.35'%3E%3Cpath d='M0 199V0h1v1.99L100 199h-1.12L1 4.22V199H0zM100 2h-.12l-1-2H100v2z'%3E%3C/path%3E%3C/g%3E%3C/svg%3E");
}

/* Fade transition CSS */
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

/* Additional component-specific styles */
</style>
