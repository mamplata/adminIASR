<template>
    <div class="relative h-screen w-screen">
        <!-- Background Image -->
        <img :src="pncBg" alt="Background" class="absolute top-0 left-0 w-full h-full object-cover" />

        <!-- Content Container -->
        <div class="flex items-center justify-center h-screen relative z-10">
            <div class="relative flex flex-col items-center">

                <!-- Card -->
                <div class="card custom-card bg-base-200 shadow-xl relative z-10">
                    <img :src="pncLogo" alt="PNC Logo" class="w-[10vw] mx-auto mt-3 h-auto mb-[-4vh] z-20" />

                    <div class="card-body custom-card-body">
                        <h2 class="card-title custom-card-title mb-[2vh] text-center">Register Device</h2>
                        <form @submit.prevent="registerDevice" class="space-y-[2vh]">
                            <input v-model="shortCode" type="text" placeholder="Enter short code"
                                class="input input-bordered w-full custom-input" required />
                            <button type="submit"
                                class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c] custom-button">
                                Register
                            </button>
                        </form>
                        <p v-if="errorMessage" class="text-red-500 mt-[2vh] text-center">{{ errorMessage }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useDeviceStore } from '@/stores/deviceStore';
import pncBg from '../assets/img/bgAnnounce.png';
import pncLogo from '../assets/img/ITAPLOGO.png';

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

<style scoped>
/* Override default card sizing with viewport-based calculations */
.custom-card {
    width: calc(20vw + 20vh);
}

.custom-card-body {
    padding: calc(2vw + 2vh);
}

.custom-card-title {
    font-size: calc(1.5vw + 1.5vh);
}

/* Override input sizing using viewport-based calculations */
.custom-input {
    font-size: calc(0.7vw + 0.7vh);
    padding: calc(1vw + 1vh);
}

/* Override button sizing using viewport-based calculations */
.custom-button {
    font-size: calc(0.7vw + 0.7vh);
    padding: calc(1vw + 1vh);
}
</style>
