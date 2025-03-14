<template>
    <div class="flex flex-col items-center p-6 space-y-6">
        <!-- Registration/loading state -->
        <div v-if="checkingRegistration" class="text-center">
            <span class="text-lg font-semibold">Checking device registration...</span>
        </div>

        <div v-else-if="!isRegistered" class="card w-full max-w-md bg-base-200 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">Register Device</h2>
                <form @submit.prevent="registerDevice" class="space-y-4">
                    <input v-model="shortCode" type="text" placeholder="Enter short code"
                        class="input input-bordered w-full" required />
                    <button type="submit" class="btn btn-success w-full">Register</button>
                </form>
                <p v-if="errorMessage" class="text-red-500 mt-2">{{ errorMessage }}</p>
            </div>
        </div>

        <!-- Device Registered & NFC Scanner view -->
        <div v-else class="card w-full max-w-lg bg-base-200 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-success mb-4">Device Registered!</h2>
                <p v-if="deviceName" class="text-lg text-gray-700 mb-4">
                    Registered Device: <strong>{{ deviceName }}</strong>
                </p>

                <!-- Info card for scanner assignment status -->
                <div v-if="scannerStatus" class="card bg-base-100 shadow-sm p-4 mb-4">
                    <div class="card-body">
                        <p>{{ scannerStatus }}</p>
                    </div>
                </div>

                <!-- NFC scanning animation -->
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
                <div v-if="scannedStudent" class="card bg-base-100 shadow-md p-4 mt-4">
                    <div class="card-body">
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
                    </div>
                </div>

                <p v-if="nfcError" class="text-red-500 mt-2">{{ nfcError }}</p>
            </div>
        </div>

        <!-- Modal for scanner assignment using daisyUI -->
        <div v-if="showModal" class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Assign Scanner Role</h3>
                <p class="py-2"><strong>Scanner:</strong> {{ newScannerInfo.uniqueKey }}</p>
                <p class="py-2"><strong>Port:</strong> {{ newScannerInfo.portPath }}</p>
                <p class="py-2">Please choose a role:</p>
                <div class="modal-action">
                    <button @click="assignRole('Time In')" class="btn btn-success">Time In</button>
                    <button @click="assignRole('Time Out')" class="btn btn-warning">Time Out</button>
                    <button @click="cancelAssignment" class="btn btn-error">Cancel</button>
                </div>
            </div>
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

// NFC read state and scanned data.
const nfcData = ref(null);
const nfcError = ref('');
const isReadingNfc = ref(false);
const scannedStudent = ref(null);

// Scanner assignment state.
const showModal = ref(false);
const newScannerInfo = ref({ uniqueKey: '', portPath: '' });
const scannerStatus = ref('');

let socket = null;

onMounted(() => {
    checkRegistration();
});

// Check device registration status and establish socket connection.
async function checkRegistration() {
    try {
        const response = await HTTP.get('/api/device/status', { withCredentials: true });
        if (response.data) {
            if (response.data.device_name) {
                deviceName.value = response.data.device_name;
            }
            // Initialize the socket connection with the device fingerprint.
            if (response.data.device_fingerprint) {
                socket = io('http://localhost:4000', {
                    query: { deviceFingerprint: response.data.device_fingerprint },
                });
                setupSocketListeners();
            }
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

// Register device with a short code.
async function registerDevice() {
    errorMessage.value = '';
    try {
        const { data } = await HTTP.post(
            '/api/device/register',
            { short_code: shortCode.value },
            { withCredentials: true }
        );
        if (data && data.success) {
            deviceName.value = data.device_name || '';
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
        errorMessage.value = error.response?.data?.message || 'An unexpected error occurred.';
    }
}

// Initiate NFC card reading.
function readNfcCard() {
    if (isReadingNfc.value) return;
    isReadingNfc.value = true;
    nfcData.value = null;
    nfcError.value = '';
    console.log("📡 Requesting to read NFC card...");
    if (socket) {
        socket.emit('readCard');
        // Also, ask the server for current stored assignments.
        socket.emit('getStoredAssignments');
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
        if (isRegistered.value) {
            setTimeout(() => {
                readNfcCard();
            }, 2000);
        }
    }
}

// Setup socket listeners for NFC and scanner assignment events.
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
        if (isRegistered.value) {
            setTimeout(() => {
                readNfcCard();
            }, 2000);
        }
    });
    // Scanner assignment events.
    socket.on('scannerDetected', (data) => {
        if (!data.assigned) {
            newScannerInfo.value = data;
            showModal.value = true;
        } else {
            scannerStatus.value = `Scanner ${data.uniqueKey} is assigned as ${data.role}.`;
        }
    });
    socket.on('scannerAssigned', (data) => {
        scannerStatus.value = `Scanner ${data.uniqueKey} assigned as ${data.role}.`;
        showModal.value = false;
    });
    socket.on('scannerAssignmentError', (data) => {
        scannerStatus.value = `Error assigning scanner ${data.uniqueKey}: ${data.message}`;
        showModal.value = false;
    });
    socket.on('scannerDisconnected', (data) => {
        scannerStatus.value = `Scanner disconnected: ${data.uniqueKey}`;
    });
}

// Functions for handling the scanner assignment modal.
function assignRole(role) {
    if (socket) {
        socket.emit('assignRole', { uniqueKey: newScannerInfo.value.uniqueKey, role });
    }
}

function cancelAssignment() {
    showModal.value = false;
}
</script>

<style scoped>
/* Additional styling if needed; daisyUI handles most of the UI classes */
</style>
