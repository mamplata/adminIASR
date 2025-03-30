<template>
    <div v-if="scannerPortStore.newScannerInfo && availableRole">
        <!-- Overlay -->
        <div class="fixed inset-0 z-[9998] bg-black opacity-50"></div>

        <!-- Scanner Assignment Modal -->
        <div
            class="fixed rounded-lg top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-200 border border-base-300 p-12 shadow-lg w-full max-w-xl text-center">
            <h3 class="text-xl font-bold mb-4">Scanner Detected</h3>
            <p class="mb-2">
                <strong>Port:</strong> {{ scannerPortStore.newScannerInfo.portPath }}
            </p>
            <p class="mb-6">Select a role to assign:</p>
            <div class="flex gap-4">
                <!-- Only allow assignment if that role is not already set -->
                <button v-if="!hasRole('Time In')" @click="onAssignRole('Time In')" class="btn btn-success flex-1">
                    Time In
                </button>
                <button v-if="!hasRole('Time Out')" @click="onAssignRole('Time Out')" class="btn btn-warning flex-1">
                    Time Out
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";

const scannerPortStore = useScannerPortStore();

// Only show the modal if at least one role is available.
const availableRole = computed(() => {
    return !scannerPortStore.timeInInfo || !scannerPortStore.timeOutInfo;
});

// Check if a given role is already assigned.
function hasRole(role) {
    return (role === "Time In" && scannerPortStore.timeInInfo) ||
        (role === "Time Out" && scannerPortStore.timeOutInfo);
}

function onAssignRole(role) {
    if (scannerPortStore.newScannerInfo) {
        scannerPortStore.assignRole(scannerPortStore.newScannerInfo.uniqueKey, role);
    }
}

onMounted(() => {
    scannerPortStore.initializeSocket();
});
</script>