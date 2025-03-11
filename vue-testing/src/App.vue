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
                <button type="submit" class="btn btn-primary w-full">Register</button>
            </form>
            <p v-if="errorMessage" class="text-red-500 mt-2">{{ errorMessage }}</p>
        </div>

        <div v-else class="w-full max-w-lg bg-base-200 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-success mb-4">Device Registered!</h2>
            <div class="flex gap-4">
                <button @click="fetchStudents" class="btn btn-secondary">Fetch Students</button>
                <!-- 🔹 Read Card Button (Disabled when reading) -->
                <button @click="readNfcCard" :disabled="isReadingNfc" class="btn btn-accent"
                    :class="{ 'btn-disabled': isReadingNfc }">
                    {{ isReadingNfc ? "Reading..." : "Read Card" }}
                </button>
            </div>

            <!-- List fetched students -->
            <div class="mt-4">
                <h3 class="text-lg font-semibold">Student List</h3>
                <ul v-if="students.length" class="list-disc pl-5 mt-2">
                    <li v-for="student in students" :key="student.studentId" class="text-sm">
                        <span class="font-bold">{{ student.fName }} {{ student.lName }}</span> -
                        {{ student.program }} ({{ student.yearLevel }} Yr)
                    </li>
                </ul>
                <div v-else class="text-gray-500">No students fetched yet.</div>
            </div>

            <!-- 🔹 Display NFC read raw data -->
            <div v-if="nfcData" class="mt-4 bg-neutral p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-white">Card Data</h3>
                <p class="text-white"><strong>UID:</strong> {{ nfcData.uid }}</p>
                <p class="text-white"><strong>Stored Data:</strong> {{ nfcData.data }}</p>
            </div>

            <!-- 🔹 Display scanned student information -->
            <div v-if="scannedStudent" class="mt-4 bg-base-300 p-4 rounded-lg shadow">
                <h3 class="text-xl font-semibold">Scanned Student Information</h3>
                <p><strong>Name:</strong> {{ scannedStudent.fName }} {{ scannedStudent.lName }}</p>
                <p><strong>Program:</strong> {{ scannedStudent.program }}</p>
                <p><strong>Year Level:</strong> {{ scannedStudent.yearLevel }}</p>
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
const errorMessage = ref('');
const students = ref([]);
const checkingRegistration = ref(true);
const socket = io('http://localhost:3000');

// NFC read state
const nfcData = ref(null);
const nfcError = ref('');
const isReadingNfc = ref(false);

// New variable to store scanned student info from scanCard API
const scannedStudent = ref(null);

onMounted(() => {
    checkRegistration();
    setupSocketListeners();
});

// Check device registration status
async function checkRegistration() {
    try {
        await HTTP.get('/api/device/status', { withCredentials: true });
        isRegistered.value = true;
    } catch (error) {
        isRegistered.value = false;
    } finally {
        checkingRegistration.value = false;
    }
}

// Register device with a short code
async function registerDevice() {
    errorMessage.value = '';
    try {
        const response = await HTTP.post(
            '/api/device/register',
            { short_code: shortCode.value },
            { withCredentials: true }
        );
        if (response.data && response.data.success) {
            isRegistered.value = true;
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'An unexpected error occurred.';
    }
}

// Fetch list of students from API
async function fetchStudents() {
    try {
        const response = await HTTP.get('/api/students', { withCredentials: true });
        students.value = response.data;
    } catch (error) {
        console.error('Error fetching students:', error);
    }
}

// Initiate NFC card reading (prevents multiple clicks)
function readNfcCard() {
    if (isReadingNfc.value) return;
    isReadingNfc.value = true;
    nfcData.value = null;
    nfcError.value = '';
    console.log("📡 Requesting to read NFC card...");
    socket.emit('readCard');
}

// After a card is read, process it through the scanCard API endpoint
async function processScannedCard(card) {
    try {
        const response = await HTTP.post(
            '/api/card/scan',
            {
                uid: card.uid,
                data: card.data
            },
            { withCredentials: true }
        );
        // On success, store the returned student info.
        scannedStudent.value = response.data.student;
        console.log("✅ Card scan processed:", response.data);
    } catch (err) {
        nfcError.value = err.response?.data?.error || 'An error occurred during card scan.';
    } finally {
        isReadingNfc.value = false;
    }
}

// Setup socket listeners for NFC events
function setupSocketListeners() {
    // On successful card read from the NFC reader.
    socket.on('cardRead', (data) => {
        console.log("✅ NFC Card Read Successfully:", data);
        nfcData.value = data;
        // Use the card data to process the scan.
        processScannedCard(data);
    });

    // On NFC read failure.
    socket.on('readFailed', (error) => {
        console.error("❌ NFC Read Failed:", error);
        nfcError.value = error.message;
        isReadingNfc.value = false;
    });
}
</script>
