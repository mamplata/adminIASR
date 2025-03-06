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
            <button @click="fetchUsers">Fetch Users</button>

            <ul v-if="users.length">
                <li v-for="user in users" :key="user.id">
                    {{ user.name }} - {{ user.email }}
                </li>
            </ul>
            <div v-else>No users fetched yet.</div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import HTTP from './http';

const shortCode = ref('');
const isRegistered = ref(false);
const errorMessage = ref('');
const users = ref([]);
const checkingRegistration = ref(true);

// 1. On component mount, try hitting a protected route
onMounted(() => {
    checkRegistration();
});

async function checkRegistration() {
    try {
        // Call the new route dedicated to device status checking
        await HTTP.get('/api/device/status', { withCredentials: true });
        isRegistered.value = true;
    } catch (error) {
        isRegistered.value = false;
    } finally {
        checkingRegistration.value = false;
    }
}

// 2. Register the device
async function registerDevice() {
    errorMessage.value = '';

    try {
        const response = await HTTP.post(
            '/api/device/register',
            { short_code: shortCode.value },
            { withCredentials: true }
        );

        // If success = true, set isRegistered
        if (response.data && response.data.success) {
            isRegistered.value = true;
        }
    } catch (error) {
        // If there's an error response from the server
        // we'll assume it has a 'message' field for us
        if (error.response && error.response.data.message) {
            errorMessage.value = error.response.data.message;
        } else {
            errorMessage.value = 'An unexpected error occurred.';
        }
    }
}

// 3. Fetch users
async function fetchUsers() {
    try {
        const response = await HTTP.get('/api/users', { withCredentials: true });
        users.value = response.data;
    } catch (error) {
        console.error('Error fetching users:', error);
    }
}
</script>