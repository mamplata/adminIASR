<template>
    <div class="card-wrapper flex justify-center items-center h-full">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'"
            class="card shadow-md mb-4 rounded-md h-[400px] lg:h-[500px] w-11/12 flex flex-col mx-auto"
            :style="{ backgroundImage: `url(${pncBg})`, backgroundSize: 'cover', position: 'relative' }">
            <!-- Logo in the top-right corner -->
            <img :src="pncLogo" alt="PNC Logo" class="absolute top-2 right-2 w-6 h-6" />

            <div class="flex flex-col flex-grow">
                <div class="m-5 card-header px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                    <h3 class="font-semibold text-white whitespace-normal break-words text-center text-2xl">
                        {{ announcement.content.title }}
                    </h3>
                </div>

                <!-- Ensuring text fits inside the fixed-height card -->
                <div class="card-body m-5 px-4 py-2 overflow-auto flex-grow">
                    <p class="text-white whitespace-normal break-words text-justify">
                        {{ announcement.content.body }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card for image announcement -->
        <div v-else-if="announcement.type === 'image'"
            class="card shadow-md mb-4 w-11/12 h-[400px] lg:h-[500px] mx-auto flex justify-center items-center">
            <img :src="`${apiUrl}${announcement.content.file_path}`" :alt="announcement.content.file_name"
                class="rounded-md w-full h-full object-cover" />
        </div>

        <!-- Fallback card -->
        <div v-else class="card shadow-md mb-4 mx-auto max-w-lg">
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
.card-body {
    padding: 1rem;
}

.text-wrap {
    white-space: normal;
    overflow-wrap: break-word;
    word-wrap: break-word;
}
</style>
