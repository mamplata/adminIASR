<template>
    <div v-if="scannerPortStore.isPortStatusModalOpen">
        <!-- Modal Overlay -->
        <div class="fixed inset-0 z-[9998] bg-black opacity-50"></div>
        <!-- Combined Modal Content -->
        <div
            class="fixed rounded-lg top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-200 border border-base-300 p-12 shadow-lg w-full max-w-xl">
            <h3 class="text-xl font-bold mb-4">Scanner Configuration</h3>

            <!-- Assignment Section -->
            <div v-if="scannerPortStore.unassignedScanners.length > 0 && availableRole" class="mb-8">
                <label class="mb-4 block">
                    <strong>Select Port:</strong>
                    <select v-model="selectedPort" class="select select-bordered w-full max-w-xs">
                        <option disabled value="">-- choose a port --</option>
                        <option v-for="scanner in scannerPortStore.unassignedScanners" :key="scanner.uniqueKey"
                            :value="scanner.uniqueKey">
                            {{ scanner.portPath }}
                        </option>
                    </select>
                </label>
                <p class="mb-4">Select a role to assign:</p>
                <div class="flex gap-4">
                    <button v-if="!hasRole('Time In')" @click="onAssignRole('Time In')" class="btn btn-success flex-1">
                        Time In
                    </button>
                    <button v-if="!hasRole('Time Out')" @click="onAssignRole('Time Out')"
                        class="btn btn-warning flex-1">
                        Time Out
                    </button>
                </div>
            </div>

            <!-- Status Section -->
            <div>
                <h4 class="font-bold text-lg mb-2">Current Assignments</h4>
                <p class="mb-2">
                    <strong>Time In:</strong>
                    <span v-if="scannerPortStore.timeInInfo">
                        <template v-if="scannerPortStore.timeInInfo.online">
                            <i class="fas fa-check text-green-500 mr-1"></i>
                            Online - Port {{ scannerPortStore.timeInInfo.portPath }}
                        </template>
                        <template v-else>
                            <i class="fas fa-times text-red-500 mr-1"></i>
                            Offline - Port {{ scannerPortStore.timeInInfo.portPath }}
                        </template>
                        <button @click="removeAssignment(scannerPortStore.timeInInfo.uniqueKey)"
                            class="btn btn-sm ml-2">
                            Remove
                        </button>
                    </span>
                    <span v-else>Not Assigned</span>
                </p>
                <p>
                    <strong>Time Out:</strong>
                    <span v-if="scannerPortStore.timeOutInfo">
                        <template v-if="scannerPortStore.timeOutInfo.online">
                            <i class="fas fa-check text-green-500 mr-1"></i>
                            Online - Port {{ scannerPortStore.timeOutInfo.portPath }}
                        </template>
                        <template v-else>
                            <i class="fas fa-times text-red-500 mr-1"></i>
                            Offline - Port {{ scannerPortStore.timeOutInfo.portPath }}
                        </template>
                        <button @click="removeAssignment(scannerPortStore.timeOutInfo.uniqueKey)"
                            class="btn btn-sm ml-2">
                            Remove
                        </button>
                    </span>
                    <span v-else>Not Assigned</span>
                </p>
            </div>

            <!-- Modal Actions -->
            <div class="modal-action mt-6">
                <button @click="closeModal" class="btn">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";

const scannerPortStore = useScannerPortStore();
const selectedPort = ref("");

// Only show assignment section if at least one role is available.
const availableRole = computed(() => {
    return !scannerPortStore.timeInInfo || !scannerPortStore.timeOutInfo;
});

// Check if a given role is already assigned.
function hasRole(role) {
    return (role === "Time In" && scannerPortStore.timeInInfo) ||
        (role === "Time Out" && scannerPortStore.timeOutInfo);
}

function onAssignRole(role) {
    if (selectedPort.value) {
        scannerPortStore.assignRole(selectedPort.value, role);
        // Update localStorage.
        let stored = localStorage.getItem("scannerAssignments");
        stored = stored ? JSON.parse(stored) : {};
        stored[selectedPort.value] = role;
        localStorage.setItem("scannerAssignments", JSON.stringify(stored));
        // Emit sync event to update the server.
        scannerPortStore.socket.emit("syncScannerAssignments", stored);
        selectedPort.value = "";
    }
}
function removeAssignment(uniqueKey) {
    scannerPortStore.removeAssignment(uniqueKey);
    let stored = localStorage.getItem("scannerAssignments");
    if (stored) {
        let assignments = JSON.parse(stored);
        delete assignments[uniqueKey];
        localStorage.setItem("scannerAssignments", JSON.stringify(assignments));
    }
}

function closeModal() {
    scannerPortStore.closePortStatusModal();
}

onMounted(() => {
    scannerPortStore.initializeSocket();
    // Sync localStorage assignments on mount.
    const stored = localStorage.getItem("scannerAssignments");
    if (stored) {
        const assignments = JSON.parse(stored);
        scannerPortStore.socket.emit("syncScannerAssignments", assignments);
    }
});
</script>