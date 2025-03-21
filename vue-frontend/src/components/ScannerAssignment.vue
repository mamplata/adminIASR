<template>
    <div v-if="newScannerInfo && newScannerInfo.uniqueKey">
        <!-- Overlay -->
        <div class="fixed inset-0 z-[9998] bg-black opacity-50"></div>

        <!-- Scanner Assignment Modal -->
        <div
            class="fixed rounded-lg top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-200 border border-base-300 p-12 shadow-lg w-full max-w-xl text-center">
            <h3 class="text-xl font-bold mb-4">Scanner Detected</h3>
            <p class="mb-2">
                <strong>Port:</strong> {{ newScannerInfo.portPath }}
            </p>
            <p class="mb-6">Please choose a role:</p>
            <div class="flex gap-4">
                <button @click="onAssignRole('Time In')" class="btn btn-success flex-1">
                    Time In
                </button>
                <button @click="onAssignRole('Time Out')" class="btn btn-warning flex-1">
                    Time Out
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getSocket } from '@/composables/socket';

const newScannerInfo = ref(null);
let socket = null;

onMounted(() => {
    socket = getSocket();
    if (!socket) {
        console.error("Socket connection is not initialized. Make sure to call initializeSocket() in a parent or registration component.");
        return;
    }

    // Listen for scanner detection
    socket.on("scannerDetected", (data) => {
        if (!data.assigned) {
            newScannerInfo.value = data;
        }
    });

    // Listen for assignment confirmation; then clear the modal
    socket.on("scannerAssigned", () => {
        newScannerInfo.value = null;
    });

    // Listen for scanner disconnect events from the server
    socket.on("scannerDisconnected", (data) => {
        console.log("Received scannerDisconnected event", data);
        if (newScannerInfo.value && newScannerInfo.value.uniqueKey === data.uniqueKey) {
            newScannerInfo.value = null;
        }
    });
});

function onAssignRole(role) {
    if (newScannerInfo.value) {
        socket.emit("assignRole", {
            uniqueKey: newScannerInfo.value.uniqueKey,
            role: role,
        });
    }
}
</script>

<style scoped>
/* Additional styles can be added if needed */
</style>
