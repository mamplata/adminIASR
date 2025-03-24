<template>
    <div class="relative h-full texture-bg">
        <!-- Header positioned in the top left corner with higher z-index -->
        <header class="absolute top-0 left-0 m-4 z-50">
            <img :src="ITAPLOGO" alt="iASR Logo" class="w-32" />
        </header>

        <!-- Main content container with adjusted top padding -->
        <div class="flex flex-col items-center justify-center h-full mx-10 pt-10">
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
                        <!-- Display Student or Error Cards -->
                        <template v-if="nfcError">
                            <!-- Error Card: Unauthorized Access -->
                            <div v-if="nfcError == 'Unauthorized access.'"
                                class="card card-side bg-error text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center h-56">
                                <figure class="z-10 flex-shrink-0 px-4">
                                    <i class="fas fa-exclamation-triangle ml-2 text-4xl"></i>
                                </figure>
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Unauthorized Access</h2>
                                    <p class="text-lg">You do not have permission to access this school.</p>
                                </div>
                            </div>

                            <!-- Error Card: Card Activation Expired -->
                            <div v-if="nfcError == 'Card is not activated'"
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
                        </template>
                        <template v-else>
                            <!-- Student Card Display -->
                            <div
                                class="card p-6 bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-3xl card-3d flex flex-col md:flex-row">
                                <figure class="z-10">
                                    <img v-if="scannedStudent.image" :src="scannedStudent.image" alt="Student Photo"
                                        class="w-48 md:w-48 object-cover" />
                                </figure>
                                <div class="card-body z-10">
                                    <h2 class="card-title">{{ scannedStudent.fName }} {{ scannedStudent.lName }}</h2>
                                    <p class="text-sm">Program: {{ scannedStudent.program }}</p>
                                    <p class="text-sm">Department: {{ scannedStudent.department }}</p>
                                    <p class="text-sm">Year Level: {{ scannedStudent.yearLevel }}</p>
                                    <p class="text-sm">Last Enrolled: {{ scannedStudent.last_enrolled_at }}</p>
                                </div>
                            </div>

                            <!-- Weekly Schedule Card -->
                            <div
                                class="card p-6 bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-3xl card-3d mt-2">
                                <div class="card-body">
                                    <span class="badge badge-xs badge-primary">Today</span>
                                    <div class="flex justify-between">
                                        <h2 class="text-3xl font-bold">Schedule</h2>
                                        <span class="text-xl">
                                            {{ new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) }}
                                        </span>
                                    </div>
                                    <ul class="mt-6 flex flex-col gap-2 text-xs" v-if="todaySchedule.length">
                                        <li v-for="item in todaySchedule" :key="item.id" class="flex items-center">
                                            <i class="fas fa-check-circle text-green-500 me-2"></i>
                                            <span>
                                                {{ item.courseCode }}: {{ item.courseDescription }} | {{ item.time }} |
                                                {{ item.room }} | Section {{ item.section }}
                                            </span>
                                        </li>
                                    </ul>
                                    <p v-else class="mt-6 text-center text-lg">{{ scheduleError }}</p>
                                </div>
                            </div>
                        </template>
                    </template>

                    <template v-else>
                        <!-- "Tap your card" prompt or loading state for scanner status -->
                        <div class="text-center">
                            <img :src="logoRFID" alt="RFID Icon" class="w-40 mx-auto"
                                :class="{ 'animate-zoom-in-out': !scannerStatusLoading && isScanEnabled }" />
                            <h1 class="text-4xl font-bold mt-4"
                                :class="{ 'text-green-900': !scannerStatusLoading && isScanEnabled, 'text-gray-500': !scannerStatusLoading && !isScanEnabled }">
                                {{ scannerStatusLoading ? 'Checking scanner status...' : (isScanEnabled ? 'Tap your card' : 'Scanner Disabled') }}
                            </h1>
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
const isLoading = ref(false);
const nfcData = ref(null);
const nfcError = ref('');

// Reactive property to track the Time In scanner status
const timeInScanner = ref(null);
// New reactive property to handle waiting state for scanner status
const scannerStatusLoading = ref(true);
// Reactive property for socket connection status
const socketConnected = ref(false);

const props = defineProps({
    deviceFingerprint: String
});
const emit = defineEmits(['scannedStudent', 'loading']);

let socket = null;

const todaySchedule = computed(() => schedule.value);

// Computed property to check if scanning is enabled (only after socket is connected and scanner status is loaded)
const isScanEnabled = computed(() => {
    if (!socketConnected.value || scannerStatusLoading.value) return false;
    return timeInScanner.value && timeInScanner.value.online;
});

function readNfcCard() {
    // Wait until scanner status loading is complete and socket is connected
    if (scannerStatusLoading.value || !socketConnected.value) {
        console.log("Waiting for scanner and socket connection...");
        setTimeout(readNfcCard, 500);
        return;
    }
    if (!isScanEnabled.value) {
        console.log("Time In scanner is not available. Scanning disabled.");
        return;
    }
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
    isLoading.value = true;
    emit('loading', true);
    let studentData; // Declare here so it’s accessible in the catch block
    try {
        const response = await HTTP.post(
            '/api/card/scan',
            { uid: card.uid, data: card.data },
            { withCredentials: true }
        );
        studentData = response.data.student;

        // Log a successful entry
        try {
            await HTTP.post('/api/entry-logs', {
                device_id: props.deviceFingerprint,
                uid: card.uid,
                student_id: studentData.studentId.toString(),
                time_type: "IN",
                status: 'Success',
                failure_reason: null
            });
        } catch (logError) {
            console.error('Failed to log entry:', logError);
        }

        const scheduleResponse = await HTTP.get(`/api/fetch-schedule/${studentData.studentId}`);
        if (scheduleResponse.data.schedule) {
            let allSchedules = Array.isArray(scheduleResponse.data.schedule)
                ? scheduleResponse.data.schedule
                : [scheduleResponse.data.schedule];
            const todayName = new Date().toLocaleDateString('en-US', { weekday: 'long' });
            const todaySchedules = allSchedules.filter(item => item.day.includes(todayName));
            if (todaySchedules.length > 0) {
                schedule.value = todaySchedules;
            } else {
                schedule.value = [];
                scheduleError.value = "No schedule available for today.";
            }
        } else {
            schedule.value = [];
            scheduleError.value = scheduleResponse.data.message || "No schedule available.";
        }
        scannedStudent.value = studentData;
        emit('scannedStudent', scannedStudent.value);
        isLoading.value = false;
        emit('loading', false);
        setTimeout(() => {
            scannedStudent.value = null;
            schedule.value = [];
            scheduleError.value = "";
            readNfcCard();
        }, 3000);
    } catch (err) {
        // Log a failure entry in case of error
        try {
            await HTTP.post('/api/entry-logs', {
                device_id: props.deviceFingerprint,
                uid: card.uid,
                student_id: studentData ? studentData.studentId.toString() : '',
                time_type: "IN",
                status: 'Failure',
                failure_reason: err.response?.data?.error || 'An error occurred during card scan.'
            });
        } catch (logError) {
            console.error('Failed to log failure entry:', logError);
        }
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

function setupSocketListeners() {
    if (!socket) return;

    socket.on('connect', () => {
        console.log("Socket connected");
        socketConnected.value = true;
    });

    // Listen for the scanner status events (Time In)
    socket.on('scannerDetected', (data) => {
        if (data.assigned && data.role === "Time In") {
            timeInScanner.value = data;
            scannerStatusLoading.value = false;
        }
    });

    socket.on('scannerAssigned', (data) => {
        if (data.role === "Time In") {
            timeInScanner.value = { ...data, online: true, role: "Time In" };
            scannerStatusLoading.value = false;
        }
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
    // Allow some time for scanner status and socket connection to initialize
    setTimeout(() => {
        scannerStatusLoading.value = false;
        readNfcCard();
    }, 3000);
});
</script>

<style scoped>
/* Background texture style */
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