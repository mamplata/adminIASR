<template>
    <div>
        <h1>Announcements</h1>
        <div v-for="announcement in announcements" :key="announcement.id">
            <h2>{{ announcement.title }}</h2>
            <p>{{ announcement.description }}</p>
            <img v-if="announcement.image_url" :src="announcement.image_url" alt="Announcement Image" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import HTTP from "@/http"; // Import Axios instance

const announcements = ref([]);

onMounted(async () => {
    try {
        const response = await HTTP.get("api/announcements");
        announcements.value = response.data.announcements;
    } catch (error) {
        console.error("Error fetching announcements:", error);
    }
});
</script>
