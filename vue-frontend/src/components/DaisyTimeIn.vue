<template>
    <div class="flex flex-col items-center justify-center h-full">
        <!-- iASR Logo -->
        <img :src="iASRPNC" alt="iASR Logo" class="w-48" />

        <!-- Card with Transition for Error or Scanned Student Info -->
        <transition name="fade">
            <div v-if="scannedStudent || nfcError" class="card bg-base-100 shadow-md p-4 mt-4 w-full max-w-md">
                <div class="card-body">
                    <template v-if="nfcError">
                        <h3 class="card-title text-red-500">{{ nfcError }}</h3>
                    </template>
                    <template v-else>
                        <h3 class="card-title">Scanned Student Information</h3>
                        <div v-if="scannedStudent.image" class="my-2">
                            <img :src="scannedStudent.image" alt="Student Image"
                                class="w-32 h-32 object-cover rounded-full" />
                        </div>
                        <p><strong>Name:</strong> {{ scannedStudent.fName }} {{ scannedStudent.lName }}</p>
                        <p><strong>Program:</strong> {{ scannedStudent.program }}</p>
                        <p><strong>Department:</strong> {{ scannedStudent.department }}</p>
                        <p><strong>Year Level:</strong> {{ scannedStudent.yearLevel }}</p>
                        <p><strong>Last Enrolled At:</strong> {{ scannedStudent.last_enrolled_at }}</p>
                    </template>
                </div>
            </div>
        </transition>

        <!-- Scanning Prompt (Shown only when there is no error and no student info) -->
        <div v-if="!scannedStudent && !nfcError" class="text-center mt-12">
            <img :src="logoRFID" alt="RFID Icon" class="w-32 mx-auto animate-zoom-in-out" />
            <h1 class="text-4xl font-bold text-green-900 mt-4">Tap your card</h1>
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
    // Do not clear scannedStudent here so that a successful scan remains visible until a new scan occurs
    console.log("📡 Requesting to read NFC card...");
    if (socket) {
        socket.emit('readCard');
        socket.emit('getStoredAssignments');
    } else {
        console.error('Socket connection not established');
    }
}

/**
 * Processes a scanned card. If successful, it updates the student info.
 * In case of error, it clears the student info, shows an error message,
 * and after 3 seconds, clears the error to display the scanning prompt.
 * In both cases, a new scan is triggered after 3 seconds.
 */
async function processScannedCard(card) {
    try {
        const response = await HTTP.post(
            '/api/card/scan',
            { uid: card.uid, data: card.data },
            { withCredentials: true }
        );
        // Update or replace the current student info with the new one.
        scannedStudent.value = response.data.student;
        // After 3 seconds, trigger the next scan.
        setTimeout(() => {
            readNfcCard();
        }, 3000);
    } catch (err) {
        nfcError.value = err.response?.data?.error || 'An error occurred during card scan.';
        scannedStudent.value = null;
        // After 3 seconds, clear error and trigger the next scan.
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
        // After 3 seconds, clear error and trigger the next scan.
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
/* Fade transition CSS */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
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
