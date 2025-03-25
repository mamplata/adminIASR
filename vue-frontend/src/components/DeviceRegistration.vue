<!-- DeviceRegistration.vue -->
<template>
    <div class="flex items-center justify-center h-screen">
        <div class="card w-full max-w-md bg-base-200 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">Register Device</h2>
                <form @submit.prevent="registerDevice" class="space-y-4">
                    <input v-model="shortCode" type="text" placeholder="Enter short code"
                        class="input input-bordered w-full" required />
                    <button type="submit"
                        class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c]">Register</button>
                </form>
                <p v-if="errorMessage" class="text-red-500 mt-2">{{ errorMessage }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useDeviceStore } from '@/stores/deviceStore';

const shortCode = ref('');
const errorMessage = ref('');
const deviceStore = useDeviceStore();

async function registerDevice() {
    errorMessage.value = '';
    const result = await deviceStore.registerDevice(shortCode.value);
    if (!result.success) {
        errorMessage.value = result.message;
    }
}
</script>