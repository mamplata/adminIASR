<template>
    <div class="flex items-center justify-center h-screen">
        <div class="card w-full max-w-md bg-base-200 shadow-xl">
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
    </div>
</template>

<script setup>
import { ref } from 'vue';
import HTTP from '@/http';
import { initializeSocket } from '@/composables/socket';

const shortCode = ref('');
const errorMessage = ref('');

// Define an event emitter to notify the parent when registration is complete.
const emit = defineEmits(['registered']);

async function registerDevice() {
    errorMessage.value = '';
    try {
        const response = await HTTP.post(
            '/api/device/register',
            { short_code: shortCode.value },
            { withCredentials: true }
        );
        if (response.data && response.data.success) {
            // Initialize the shared socket connection using the device fingerprint.
            if (response.data.device_fingerprint) {
                initializeSocket(response.data.device_fingerprint);
            }
            // Emit the device info to the parent component.
            emit('registered', {
                deviceName: response.data.device_name || '',
                deviceFingerprint: response.data.device_fingerprint || '',
            });
        }
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message || 'An unexpected error occurred.';
    }
}
</script>
