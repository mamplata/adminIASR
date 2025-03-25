<!-- ScannerAssignment.vue -->
<template>
    <div v-if="scannerPortStore.newScannerInfo && scannerPortStore.newScannerInfo.uniqueKey">
        <!-- Overlay -->
        <div class="fixed inset-0 z-[9998] bg-black opacity-50"></div>

        <!-- Scanner Assignment Modal -->
        <div
            class="fixed rounded-lg top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-200 border border-base-300 p-12 shadow-lg w-full max-w-xl text-center">
            <h3 class="text-xl font-bold mb-4">Scanner Detected</h3>
            <p class="mb-2">
                <strong>Port:</strong> {{ scannerPortStore.newScannerInfo.portPath }}
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
import { onMounted } from "vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";

const scannerPortStore = useScannerPortStore();

// Initialize socket listeners via the store on mount.
onMounted(() => {
    scannerPortStore.initializeSocket();
});

function onAssignRole(role) {
    if (scannerPortStore.newScannerInfo) {
        scannerPortStore.assignRole(scannerPortStore.newScannerInfo.uniqueKey, role);
    }
}
</script>

<style scoped>
/* Additional styles if needed */
</style>