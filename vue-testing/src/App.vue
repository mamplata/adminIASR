<template>
    <div class="flex flex-col items-center p-6">
        <div v-if="checkingRegistration" class="text-center text-lg font-semibold">
            Checking device registration...
        </div>

        <div v-else-if="!isRegistered" class="w-full max-w-md bg-base-200 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Register Device</h2>
            <form @submit.prevent="registerDevice" class="space-y-4">
                <input v-model="shortCode" class="input input-bordered w-full" placeholder="Enter short code"
                    required />
                <button type="submit" class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                    Register
                </button>
            </form>
            <p v-if="errorMessage" class="text-red-500 mt-2">{{ errorMessage }}</p>
        </div>

        <div v-else class="w-full max-w-lg bg-base-200 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-success mb-4">
                Device Registered!
            </h2>
            <!-- Display the device name -->
            <p v-if="deviceName" class="text-lg text-gray-700 mb-4">
                Registered Device: <strong>{{ deviceName }}</strong>
            </p>

            <!-- Live scanning animation -->
            <div class="flex flex-col items-center my-4">
                <div v-if="isReadingNfc" class="flex items-center space-x-2">
                    <svg class="animate-spin h-8 w-8 text-primary" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Scanning for NFC card...</span>
                </div>
                <div v-else>
                    <span class="text-lg text-gray-600">Waiting for card scan...</span>
                </div>
            </div>

            <!-- Display scanned student information -->
            <div v-if="scannedStudent" class="mt-4 bg-base-300 p-4 rounded-lg shadow">
                <h3 class="text-xl font-semibold">Scanned Student Information</h3>
                <div v-if="scannedStudent.image" class="mt-2">
                    <img :src="scannedStudent.image" alt="Student Image" class="w-32 h-32 object-cover rounded-full">
                </div>
                <p><strong>Name:</strong> {{ scannedStudent.fName }} {{ scannedStudent.lName }}</p>
                <p><strong>Program:</strong> {{ scannedStudent.program }}</p>
                <p><strong>Department:</strong> {{ scannedStudent.department }}</p>
                <p><strong>Year Level:</strong> {{ scannedStudent.yearLevel }}</p>
                <p><strong>Last Enrolled At:</strong> {{ scannedStudent.last_enrolled_at }}</p>
            </div>

            <p v-if="nfcError" class="text-red-500 mt-2">{{ nfcError }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { io } from 'socket.io-client';
import HTTP from './http';

const shortCode = ref('');
const isRegistered = ref(false);
const deviceName = ref('');
const errorMessage = ref('');
const checkingRegistration = ref(true);

// We'll create the socket connection after we obtain the device fingerprint.
let socket = null;

// NFC read state and scanned data.
const nfcData = ref(null);
const nfcError = ref('');
const isReadingNfc = ref(false);
const scannedStudent = ref(null);

onMounted(() => {
    checkRegistration();
});

// Check device registration status and establish socket connection with fingerprint.
async function checkRegistration() {
    try {
        const response = await HTTP.get('/api/device/status', { withCredentials: true });
        if (response.data) {
            if (response.data.device_name) {
                deviceName.value = response.data.device_name;
            }
            // Ensure the API returns the device fingerprint (set in your Laravel middleware).
            if (response.data.device_fingerprint) {
                socket = io('http://localhost:4000', {
                    query: { deviceFingerprint: response.data.device_fingerprint },
                });
                setupSocketListeners();
            }

            console.log(response.data.device_fingerprint);
            isRegistered.value = true;
        }
    } catch (error) {
        isRegistered.value = false;
    } finally {
        checkingRegistration.value = false;
        if (isRegistered.value) {
            readNfcCard();
        }
    }
}

// Register device with a short code and initialize socket with fingerprint.
async function registerDevice() {
    errorMessage.value = '';
    try {
        const { data } = await HTTP.post(
            '/api/device/register',
            { short_code: shortCode.value },
            { withCredentials: true }
        );
        if (data && data.success) {
            // Set device name if available.
            deviceName.value = data.device_name || '';

            // Initialize the socket connection with the device fingerprint.
            if (data.device_fingerprint) {
                socket = io('http://localhost:4000', {
                    query: { deviceFingerprint: data.device_fingerprint },
                });
                setupSocketListeners();
            }

            isRegistered.value = true;
            readNfcCard();
        }
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'An unexpected error occurred.';
    }
}

// Initiate NFC card reading. Prevent multiple concurrent scans.
function readNfcCard() {
    if (isReadingNfc.value) return;
    isReadingNfc.value = true;
    nfcData.value = null;
    nfcError.value = '';
    console.log("📡 Requesting to read NFC card...");
    if (socket) {
        socket.emit('readCard');
    } else {
        console.error('Socket connection not established');
    }
}

// Process the scanned card via the scanCard API endpoint.
async function processScannedCard(card) {
    try {
        const response = await HTTP.post(
            '/api/card/scan',
            { uid: card.uid, data: card.data },
            { withCredentials: true }
        );
        scannedStudent.value = response.data.student;
    } catch (err) {
        nfcError.value = err.response?.data?.error || 'An error occurred during card scan.';
        scannedStudent.value = null;
    } finally {
        isReadingNfc.value = false;
        // Automatically restart scanning after a 2-second delay.
        if (isRegistered.value) {
            setTimeout(() => {
                readNfcCard();
            }, 2000);
        }
    }
}

// Setup socket listeners for NFC events.
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
        // Restart scanning after an error.
        if (isRegistered.value) {
            setTimeout(() => {
                readNfcCard();
            }, 2000);
        }
    });
}
</script>
