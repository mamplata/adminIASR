<template>
    <div class="flex flex-col items-center p-6">
        <div v-if="checkingRegistration" class="text-center text-lg font-semibold">
            Checking device registration...
        </div>

        <div v-else-if="!isRegistered" class="w-full max-w-md bg-base-200 p-6 rounded-lg shadow-lg">
            <button class="btn btn-soft">dDasd</button>
            <h1 class="text-3xl font-bold text-primary mb-6">Hello Vue!</h1>

            <h2 class="text-xl font-semibold mb-4">Register Device</h2>
            <form @submit.prevent="registerDevice" class="space-y-4">
                <input v-model="shortCode" class="input input-bordered w-full" placeholder="Enter short code"
                    required />
                <button type="submit" class="btn btn-primary w-full">Register</button>
            </form>
            <p v-if="errorMessage" class="text-red-500 mt-2">{{ errorMessage }}</p>
        </div>

        <div v-else class="w-full max-w-lg bg-base-200 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-success mb-4">Device Registered!</h2>
            <div class="flex gap-4">
                <button @click="fetchStudents" class="btn btn-secondary">Fetch Students</button>

                <!-- 🔹 Read Card Button (Disabled when reading) -->
                <button @click="readNfcCard" :disabled="isReadingNfc" class="btn btn-accent"
                    :class="{ 'btn-disabled': isReadingNfc }">
                    {{ isReadingNfc ? "Reading..." : "Read Card" }}
                </button>
            </div>

            <div class="mt-4">
                <h3 class="text-lg font-semibold">Student List</h3>
                <ul v-if="students.length" class="list-disc pl-5 mt-2">
                    <li v-for="student in students" :key="student.studentId" class="text-sm">
                        <span class="font-bold">{{ student.fName }} {{ student.lName }}</span> -
                        {{ student.program }} ({{ student.yearLevel }} Yr)
                    </li>
                </ul>
                <div v-else class="text-gray-500">No students fetched yet.</div>
            </div>

            <!-- 🔹 NFC Read Results -->
            <div v-if="nfcData" class="mt-4 bg-neutral p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-white">Card Data</h3>
                <p class="text-white"><strong>UID:</strong> {{ nfcData.uid }}</p>
                <p class="text-white"><strong>Stored Data:</strong> {{ nfcData.data }}</p>
            </div>

            <p v-if="nfcError" class="text-red-500 mt-2">{{ nfcError }}</p>
        </div>
    </div>
</template>

<script setup>
import RFIDScanner from './components/RFIDScanner.vue';
</script>
