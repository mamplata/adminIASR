<template>
    <div class="relative h-full">
        <!-- Header positioned in the top left corner -->
        <header class="absolute top-0 left-0 m-4">
            <img :src="iASRPNC" alt="iASR Logo" class="w-64" />
        </header>

        <!-- Main content container -->
        <div class="flex flex-col items-center justify-center h-full">
            <transition name="fade">
                <div v-if="scannedStudent || nfcError"
                    class="p-6 w-96 mx-auto rounded-lg uppercase bg-base-100 shadow-md">
                    <!-- Error State -->
                    <template v-if="nfcError">
                        <h3 class="text-red-500 text-center">{{ nfcError }}</h3>
                    </template>

                    <!-- Student Information -->
                    <template v-else>
                        <!-- Primary Information Section -->
                        <section class="flex flex-col items-center justify-around gap-4">
                            <!-- Student Image -->
                            <div v-if="scannedStudent.image" class="flex-shrink-0">
                                <img :src="scannedStudent.image" alt="Student Image"
                                    class="w-40 h-40 rounded-full object-cover shadow-lg" />
                            </div>

                            <!-- Essential Details -->
                            <div class="flex flex-col text-center">
                                <div>
                                    <span>{{ scannedStudent.fName }} {{ scannedStudent.lName }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-sm">{{ scannedStudent.studentId }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Additional Information Section -->
                        <section class="mt-4 text-justify gap-4 space-y-3">
                            <div>
                                <strong>Program: </strong>
                                <span>{{ scannedStudent.program }}</span>
                            </div>
                            <div>
                                <strong>Department: </strong>
                                <span>{{ scannedStudent.department }}</span>
                            </div>
                            <div>
                                <strong>Year Level: </strong>
                                <span>{{ scannedStudent.yearLevel }}</span>
                            </div>
                            <div>
                                <strong>Last Enrolled At: </strong>
                                <span>{{ scannedStudent.last_enrolled_at }}</span>
                            </div>
                        </section>
                    </template>
                </div>
            </transition>

            <!-- Scanning Prompt (Shown only when there is no error and no student info) -->
            <div v-if="!scannedStudent && !nfcError" class="text-center mt-12">
                <img :src="logoRFID" alt="RFID Icon" class="w-40 mx-auto animate-zoom-in-out" />
                <h1 class="text-4xl font-bold text-green-900 mt-4">
                    {{ isReadingNfc ? "Scanning..." : "Tap your card" }}
                </h1>
                <p class="text-gray-600 mt-2" v-if="scanCount > 0">Scans: {{ scanCount }}</p>
                <!-- Added spinner icon for "Ready to scan" status -->
                <p class="text-green-700 font-semibold mt-1 flex items-center">
                    <svg v-if="readyStatus === 'Ready to scan'" class="animate-spin h-5 w-5 mr-2 text-green-700"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    {{ readyStatus }}
                </p>
            </div>
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
        }, 1000);
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
