<template>
    <div class="relative flex items-center justify-center h-40 w-full bg-cover bg-center"
        :style="{ backgroundImage: `url(${pncBg})` }">
        <div class="text-white text-center p-4 bg-opacity-50 bg-black rounded-lg">
            <h1 class="text-3xl font-bold">{{ time }}</h1>
            <h4 class="text-lg">{{ date }}</h4>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import pncBg from "@/assets/img/pnc-bg.jpg";

const time = ref("");
const date = ref("");

const updateTime = () => {
    const now = new Date();
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    const gmt8 = new Date(utc + 8 * 3600000);

    time.value = gmt8.toLocaleTimeString("en-US", { hour12: false });
    date.value = gmt8.toLocaleDateString("en-US", { weekday: "short", day: "numeric", month: "short", year: "numeric" });
};

onMounted(() => {
    updateTime();
    setInterval(updateTime, 1000);
});
</script>
