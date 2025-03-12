<template>
    <div class="overflow-x-auto">
        <table class="table w-full bg-base-100 shadow-md rounded-lg">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Department</th>
                    <th>Year Level</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(log, index) in logs" :key="index" class="hover:bg-gray-100">
                    <td><img :src="log.photo" class="w-10 h-10 rounded-full" alt="User"></td>
                    <td>{{ log.name }}</td>
                    <td>{{ log.program }}</td>
                    <td>{{ log.department }}</td>
                    <td>{{ log.yearLevel }}</td>
                    <td>{{ log.timestamp }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref } from "vue";

const logs = ref([
    { photo: "hehe", name: "Kyle Mamplata", program: "IT", department: "CCS", yearLevel: "4th Year", timestamp: "00:04:03-00:04:07" },
    { photo: "hehe", name: "Raven Lagmay", program: "IT", department: "CCS", yearLevel: "4th Year", timestamp: "00:04:04-00:04:07" }
]);
</script>
