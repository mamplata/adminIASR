<template>
    <div class="flex flex-col items-center justify-center h-full">
        <img :src="iASRPNC" alt="iASR Logo" class="w-48" />
        <div v-if="!cardTapped" class="text-center mt-12">
            <img :src="logoRFID" alt="RFID Icon" class="w-32 mx-auto animate-zoom-in-out" />
            <h1 class="text-4xl font-bold text-green-900 mt-4">Tap your card</h1>
        </div>
        <div v-else class="text-center mt-12">
            <!-- Card ID -->
            <div class="border border-black rounded-lg p-4 text-center text-lg font-semibold w-64 mb-20">
                Card ID
            </div>
            <!-- Schedule -->
            <div class="border border-black rounded-lg p-4 text-center text-lg font-semibold w-64">
                Schedule
            </div>
        </div>
        <button @click="tapCard" class="mt-4 px-4 py-2 bg-green-600 text-white rounded">
            Simulate Card Tap
        </button>
    </div>
</template>

<script setup>
import { ref } from "vue";
import iASRPNC from "@/assets/img/iASRPNC.png";
import logoRFID from "@/assets/img/logoRFID.png";

const cardTapped = ref(false);

const tapCard = () => {
    cardTapped.value = !cardTapped.value;
};
</script>

<style scoped>
/* You can add styles specific to the left section here if needed */
</style>
