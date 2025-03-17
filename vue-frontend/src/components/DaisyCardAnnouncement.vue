<template>
    <div class="card-wrapper">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'" class="card shadow-md mb-4 rounded-md"
            :style="{ backgroundImage: 'url(' + pncBg + ')', backgroundSize: 'cover', position: 'relative' }">
            <!-- Logo in the top-right corner -->
            <img :src="pncLogo" alt="PNC Logo" class="absolute top-2 right-2 w-6 h-6" />

            <div>
                <div class="m-5 card-header px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                    <h3 class="font-semibold text-white whitespace-normal break-words text-center text-2xl">
                        {{ announcement.content.title }}
                    </h3>
                </div>
                <div class="card-body m-5 px-4 py-2">
                    <p class="text-white whitespace-normal break-words text-justify">
                        {{ announcement.content.body }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card for image announcement -->
        <div v-else-if="announcement.type === 'image'" class="card shadow-md mb-4">
            <img :src="`${apiUrl}${announcement.content.file_path}`" :alt="announcement.content.file_name"
                class="rounded-md w-full object-cover" />
        </div>

        <!-- Fallback card -->
        <div v-else class="card shadow-md mb-4">
            <div class="card-body px-4 py-2">
                <p class="text-gray-600 dark:text-gray-400">
                    No preview available for this announcement.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import HTTP from "@/http"; // Axios instance
import pncBg from "../assets/img/pnc-bg.jpg";
import pncLogo from "../assets/img/pnc-logo-1.png";

const apiUrl = import.meta.env.VITE_API_URL;
const announcement = ref(null);

const fetchAnnouncement = async () => {
    try {
        const response = await HTTP.get("/api/announcements");
        console.log(response.value);
        announcement.value = response.data.announcements[0]; // Fetching the first announcement
    } catch (error) {
        console.error("Error fetching announcement:", error);
    }
};

onMounted(fetchAnnouncement);
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
