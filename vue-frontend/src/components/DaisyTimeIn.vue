<template>
    <div class="relative h-full texture-bg">
        <!-- Header positioned in the top left corner -->
        <header class="absolute top-0 left-0 m-4">
            <img :src="iASRPNC" alt="iASR Logo" class="w-64" />
        </header>

        <!-- Main content container -->
        <div class="flex flex-col items-center justify-center h-full mx-10">
            <!-- Single transition wrapping both states with a keyed container -->
            <transition name="fade" mode="out-in">
                <div :key="(scannedStudent || nfcError) ? 'card' : 'prompt'">
                    <template v-if="scannedStudent || nfcError">
                        <!-- Error State -->
                        <template v-if="nfcError">
                            <!-- Error Card: Unauthorized Access with 3D effect -->
                            <div v-if="nfcError == 'Unauthorized access'"
                                class="card card-side bg-error text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center my-4 h-64">
                                <!-- Background Pattern Overlay -->
                                <div class="absolute inset-0 z-0"
                                    style="background-image: url('https://www.transparenttextures.com/patterns/bubbles.png'); opacity: 0.15;">
                                </div>
                                <!-- Icon Section -->
                                <figure class="z-10 flex-shrink-0 p-4">
                                    <i class="fas fa-exclamation-triangle text-5xl"></i>
                                </figure>
                                <!-- Message Section -->
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Unauthorized Access</h2>
                                    <p class="text-lg">
                                        You do not have permission to access this resource.
                                    </p>
                                </div>
                            </div>

                            <!-- Error Card: Card Activation Expired with 3D effect -->
                            <div v-if="nfcError == 'Card is not activated'"
                                class="card card-side bg-warning text-white shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-md card-3d flex flex-col md:flex-row items-center justify-center my-4 h-64">
                                <!-- Background Pattern Overlay -->
                                <div class="absolute inset-0 z-0"
                                    style="background-image: url('https://www.transparenttextures.com/patterns/bubbles.png'); opacity: 0.15;">
                                </div>
                                <!-- Icon Section -->
                                <figure class="z-10 flex-shrink-0 p-4">
                                    <i class="fas fa-info-circle text-5xl"></i>
                                </figure>
                                <!-- Message Section -->
                                <div class="card-body z-10">
                                    <h2 class="card-title text-2xl">Card Activation Expired</h2>
                                    <p class="text-lg">
                                        Your card activation period has expired. Please contact support for further
                                        assistance.
                                    </p>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <!-- Student Card Display -->
                            <div
                                class="card p-6 bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300 relative overflow-hidden w-full max-w-3xl card-3d flex flex-col md:flex-row">
                                <!-- Background Pattern Overlay -->
                                <div class="absolute inset-0 z-0"
                                    style="background-image: url('https://www.transparenttextures.com/patterns/bubbles.png'); opacity: 0.15;">
                                </div>

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
import { ref, onMounted } from 'vue';
import { getSocket } from '@/composables/socket';
import iASRPNC from '@/assets/img/iASRPNC.png';
import logoRFID from '@/assets/img/logoRFID.png';
import HTTP from '@/http'; // adjust the path to your axios instance

const scannedStudent = ref(null);
const isReadingNfc = ref(false);
const nfcData = ref(null);
const nfcError = ref('');

// Accept deviceFingerprint as a prop (if needed for display or other logic)
const props = defineProps({
    deviceFingerprint: String
});

let socket = null;

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

/**
 * Processes a scanned card.
 */
async function processScannedCard(card) {
    try {
        const response = await HTTP.post(
            '/api/card/scan',
            { uid: card.uid, data: card.data },
            { withCredentials: true }
        );
        scannedStudent.value = response.data.student;
        // After showing student info, clear it to revert to the "Tap your card" state
        setTimeout(() => {
            scannedStudent.value = null;
            readNfcCard();
        }, 3000);
    } catch (err) {
        nfcError.value = err.response?.data?.error || 'An error occurred during card scan.';
        scannedStudent.value = null;
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
