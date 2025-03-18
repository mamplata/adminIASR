<template>
    <div class="relative h-full texture-bg">
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
                <h1 class="text-4xl font-bold text-green-900 mt-4">Tap your card</h1>
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
        setTimeout(() => {
            readNfcCard();
        }, 1000);
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
