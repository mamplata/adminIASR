<template>
    <div v-if="scannerPortStore.isPortStatusModalOpen">
        <!-- Modal Overlay -->
        <div class="fixed inset-0 z-[9998] bg-black opacity-50"></div>
        <!-- Combined Modal Content -->
        <div
            class="fixed rounded-lg top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[9999] bg-base-200 border border-base-300 p-[calc(2vw+2vh)] shadow-lg w-full max-w-[40vw]">
            <h3 class="text-[calc(1.2vw+1.2vh)] font-bold mb-[1vh]">Scanner Configuration</h3>

            <!-- Assignment Section -->
            <div v-if="scannerPortStore.unassignedScanners.length > 0 && availableRole" class="mb-[2.5vh]">
                <label class="mb-[1vh] block">
                    <strong class="text-[calc(0.8vw+0.8vh)]">Select Port: </strong>
                    <select v-model="selectedPort"
                        class="select select-bordered text-[calc(0.8vw+0.8vh)] h-[4.5vh] w-[34vw]">
                        <option class="text-[calc(0.8vw+0.8vh)]" disabled value="">-- choose a port --</option>
                        <option v-for="scanner in scannerPortStore.unassignedScanners" :key="scanner.uniqueKey"
                            :value="scanner.uniqueKey">
                            {{ scanner.portPath }}
                        </option>
                    </select>
                </label>
                <p class="mb-[1vh] text-[calc(0.8vw+0.8vh)]">Select a role to assign:</p>
                <div class="flex gap-[1vw]">
                    <button v-if="!hasRole('Time In')" @click="onAssignRole('Time In')"
                        class="btn btn-success p-[calc(0.5vw+0.5vh)] text-[calc(0.8vw+0.8vh)] h-[5vh] flex-1">
                        Time In
                    </button>
                    <button v-if="!hasRole('Time Out')" @click="onAssignRole('Time Out')"
                        class="btn btn-warning p-[calc(0.5vw+0.5vh)] text-[calc(0.8vw+0.8vh)] h-[5vh] flex-1">
                        Time Out
                    </button>
                </div>
            </div>

            <!-- Status Section -->
            <div>
                <h4 class="font-bold text-[calc(1.2vw+1.2vh)] mb-[0.5vh]">Assignments</h4>
                <p class="mb-[0.5vh]">
                    <strong class="text-[calc(0.8vw+0.8vh)]">Time In:</strong>
                    <span class="text-[calc(0.8vw+0.8vh)] ml-[0.5vw]" v-if="scannerPortStore.timeInInfo">
                        <template v-if="scannerPortStore.timeInInfo.online">
                            <i class="fas fa-check text-green-500 mr-[0.5vw]"></i>
                            Online - Port {{ scannerPortStore.timeInInfo.portPath }}
                        </template>
                        <template v-else>
                            <i class="fas fa-times text-red-500 mr-[0.5vw]"></i>
                            Offline - Port {{ scannerPortStore.timeInInfo.portPath }}
                        </template>
                        <button @click="removeAssignment(scannerPortStore.timeInInfo.uniqueKey)"
                            class="btn ml-[0.5vw] bg-red-500 rounded-[calc(0.5vw+0.5vh)] p-[calc(0.5vw+0.5vh)] text-white text-[calc(0.7vw+0.7vh)] h-[4vh]">
                            Remove
                        </button>
                    </span>
                    <span class="text-[calc(0.8vw+0.8vh)]" v-else>
                        <i class="fas fa-plug-circle-xmark text-gray-500 ml-[0.5vw] mr-[0.5vw]"></i> Not Assigned
                    </span>
                </p>
                <p>
                    <strong class="text-[calc(0.8vw+0.8vh)]">Time Out:</strong>
                    <span class="text-[calc(0.8vw+0.8vh)] ml-[0.5vw]" v-if="scannerPortStore.timeOutInfo">
                        <template v-if="scannerPortStore.timeOutInfo.online">
                            <i class="fas fa-check text-green-500 mr-[0.5vw]"></i>
                            Online - Port {{ scannerPortStore.timeOutInfo.portPath }}
                        </template>
                        <template v-else>
                            <i class="fas fa-times text-red-500 mr-[0.5vw]"></i>
                            Offline - Port {{ scannerPortStore.timeOutInfo.portPath }}
                        </template>
                        <button @click="removeAssignment(scannerPortStore.timeOutInfo.uniqueKey)"
                            class="btn ml-[0.5vw] bg-red-500 rounded-[calc(0.5vw+0.5vh)] p-[calc(0.5vw+0.5vh)] text-white text-[calc(0.7vw+0.7vh)] h-[4vh]">
                            Remove
                        </button>
                    </span>
                    <span class="text-[calc(0.8vw+0.8vh)]" v-else>
                        <i class="fas fa-plug-circle-xmark text-gray-500 ml-[0.5vw] mr-[0.5vw]"></i> Not Assigned
                    </span>
                </p>
            </div>

            <!-- Modal Actions -->
            <div class="modal-action mt-[2vh]">
                <button @click="closeModal"
                    class="btn rounded-[calc(0.3vw+0.3vh)] p-[calc(0.7vw+0.7vh)] text-[calc(0.8vw+0.8vh)] h-[6vh]">Close</button>
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