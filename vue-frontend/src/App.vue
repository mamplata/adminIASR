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

            <ul v-if="students.length">
                <li v-for="student in students" :key="student.studentId">
                    {{ student.fName }} {{ student.lName }} - {{ student.program }} ({{ student.yearLevel }} Yr)
                </li>
            </ul>
            <div v-else>No students fetched yet.</div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import HTTP from './http';

const shortCode = ref('');
const isRegistered = ref(false);
const errorMessage = ref('');
const students = ref([]);  // ✅ Changed 'users' to 'students'
const checkingRegistration = ref(true);

onMounted(() => {
    checkRegistration();
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

// ✅ Fetch students instead of users
async function fetchStudents() {
    try {
        const response = await HTTP.get('/api/students', { withCredentials: true });
        students.value = response.data;
    } catch (error) {
        console.error('Error fetching students:', error);
    }
}
</script>
