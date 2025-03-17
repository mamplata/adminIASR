<!-- DaisyCardAnnouncement.vue -->
<template>
    <div class="card-wrapper flex justify-center items-center w-full h-full">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'" class="card shadow-md rounded-md w-full h-full flex flex-col relative"
            :style="{ backgroundImage: `url(${pncBg})`, backgroundSize: 'cover', backgroundPosition: 'center' }">
            <!-- Logo in the top-right corner -->
            <img :src="pncLogo" alt="PNC Logo" class="absolute top-2 right-2 w-6 h-6" />

            <div class="flex flex-col flex-grow">
                <div class="card-header px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                    <h3 class="font-semibold text-white whitespace-normal break-words text-center text-xl">
                        {{ announcement.content.title }}
                    </h3>
                </div>
                <!-- Ensuring text fits inside the card -->
                <div class="card-body px-4 py-2 overflow-auto flex-grow">
                    <p class="text-white whitespace-normal break-words text-justify">
                        {{ announcement.content.body }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card for image announcement -->
        <div v-else-if="announcement.type === 'image'"
            class="card shadow-md w-full h-full flex justify-center items-center relative">
            <img :src="`${apiUrl}${announcement.content.file_path}`" :alt="announcement.content.file_name"
                class="rounded-md w-full h-full object-cover" />
        </div>

        <!-- Fallback card -->
        <div v-else class="card shadow-md w-full h-full flex justify-center items-center">
            <div class="card-body px-4 py-2">
                <p class="text-gray-600 dark:text-gray-400">
                    No preview available for this announcement.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import pncBg from '../../assets/img/pnc-bg.jpg';
import pncLogo from '../../assets/img/pnc-logo-1.png';

const apiUrl = import.meta.env.VITE_API_URL;

const props = defineProps({
    announcement: {
        type: Object,
        required: true,
    },
});
</script>

<style scoped>
/* Make sure the card and its wrapper don’t exceed their container */
.card-wrapper,
.card {
    max-height: 100%;
    overflow: hidden;
}
</style>
