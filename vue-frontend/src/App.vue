<template>
    <div>
        <div v-if="checkingRegistration">
            Checking device registration...
        </div>

        <div v-else-if="!isRegistered">
            <h2>Register Device</h2>
            <form @submit.prevent="registerDevice">
                <input v-model="shortCode" placeholder="Enter short code" required />
                <button type="submit">Register</button>
            </form>
            <p v-if="errorMessage" style="color: red;">{{ errorMessage }}</p>
        </div>

        <div v-else>
            <h2>Device Registered!</h2>
            <button @click="fetchStudents">Fetch Students</button>

            <!-- 🔹 Read Card Button (Disabled when reading) -->
            <button @click="readNfcCard" :disabled="isReadingNfc">
                {{ isReadingNfc ? "Reading..." : "Read Card" }}
            </button>

            <ul v-if="students.length">
                <li v-for="student in students" :key="student.studentId">
                    {{ student.fName }} {{ student.lName }} - {{ student.program }} ({{ student.yearLevel }} Yr)
                </li>
            </ul>
            <div v-else>No students fetched yet.</div>

            <!-- 🔹 NFC Read Results -->
            <div v-if="nfcData">
                <h3>Card Data</h3>
                <p><strong>UID:</strong> {{ nfcData.uid }}</p>
                <p><strong>Stored Data:</strong> {{ nfcData.data }}</p>
            </div>

            <p v-if="nfcError" style="color: red;">{{ nfcError }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { io } from 'socket.io-client'; // 🔹 Import Socket.IO
import HTTP from './http';

const shortCode = ref('');
const isRegistered = ref(false);
const errorMessage = ref('');
const students = ref([]);
const checkingRegistration = ref(true);
const socket = io('http://localhost:3000'); // 🔹 Connect to Socket.IO server

// ✅ NFC Read State
const nfcData = ref(null);
const nfcError = ref('');
const isReadingNfc = ref(false); // 🔹 Prevents multiple reads

// ✅ Lifecycle Hook
onMounted(() => {
    checkRegistration();
    setupSocketListeners();
});

// ✅ Check if device is registered
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

// ✅ Register device
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

// ✅ Fetch students
async function fetchStudents() {
    try {
        const response = await HTTP.get('/api/students', { withCredentials: true });
        students.value = response.data;
    } catch (error) {
        console.error('Error fetching students:', error);
    }
}

// ✅ Read NFC Card (Prevents Spam Clicks)
function readNfcCard() {
    if (isReadingNfc.value) return; // 🔹 Prevent multiple clicks
    isReadingNfc.value = true;
    nfcData.value = null;
    nfcError.value = '';
    console.log("📡 Requesting to read NFC card...");

    socket.emit('readCard'); // 🔹 Emit event to start reading
}

// ✅ Setup Socket Listeners
function setupSocketListeners() {
    // 🔹 Listen for successful NFC card read
    socket.on('cardRead', (data) => {
        console.log("✅ NFC Card Read Successfully:", data);
        nfcData.value = data;
        isReadingNfc.value = false; // 🔹 Enable button again
    });

    // 🔹 Listen for NFC read failure
    socket.on('readFailed', (error) => {
        console.error("❌ NFC Read Failed:", error);
        nfcError.value = error.message;
        isReadingNfc.value = false; // 🔹 Enable button again
    });
}
</script>
