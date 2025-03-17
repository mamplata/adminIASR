<template>
    <div class="modal modal-open">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Port and Device Status</h3>
            <p class="py-2"><strong>Device:</strong> {{ deviceName }}</p>
            <div class="py-2">
                <p>
                    <strong>Time In: </strong>
                    <span>
                        <template v-if="timeInInfo">
                            <template v-if="timeInInfo.online">
                                <i class="fas fa-check text-green-500 mr-1"></i>
                                Online - Port {{ timeInInfo.portPath }}
                            </template>
                            <template v-else>
                                <i class="fas fa-times text-red-500 mr-1"></i>
                                Offline - Port {{ timeInInfo.portPath }}
                            </template>
                        </template>
                        <template v-else>
                            <i class="fas fa-times text-red-500 mr-1"></i>
                            Not Connected
                        </template>
                    </span>
                </p>
                <p>
                    <strong>Time Out: </strong>
                    <span>
                        <template v-if="timeOutInfo">
                            <template v-if="timeOutInfo.online">
                                <i class="fas fa-check text-green-500 mr-1"></i>
                                Online - Port {{ timeOutInfo.portPath }}
                            </template>
                            <template v-else>
                                <i class="fas fa-times text-red-500 mr-1"></i>
                                Offline - Port {{ timeOutInfo.portPath }}
                            </template>
                        </template>
                        <template v-else>
                            <i class="fas fa-times text-red-500 mr-1"></i>
                            Not Connected
                        </template>
                    </span>
                </p>
            </div>

            <!-- Scanner assignment section -->
            <div v-if="newScannerInfo && newScannerInfo.uniqueKey" class="border-t pt-4 mt-4">
                <h3 class="font-bold text-lg">Assign Scanner Role</h3>
                <p class="py-2"><strong>Scanner:</strong> {{ newScannerInfo.uniqueKey }}</p>
                <p class="py-2"><strong>Port:</strong> {{ newScannerInfo.portPath }}</p>
                <p class="py-2">Please choose a role:</p>
                <div class="modal-action">
                    <button @click="onAssignRole('Time In')" class="btn btn-success">Time In</button>
                    <button @click="onAssignRole('Time Out')" class="btn btn-warning">Time Out</button>
                </div>
            </div>

            <div class="modal-action mt-4">
                <button @click="$emit('close')" class="btn">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    deviceName: { type: String, required: true },
    timeInInfo: { type: Object, default: null },
    timeOutInfo: { type: Object, default: null },
    newScannerInfo: { type: Object, default: null }
});

// Emit events for closing the modal and for assignment actions.
const emit = defineEmits(['close', 'assignRole']);

function onAssignRole(role) {
    // Emit the assignRole event with the chosen role.
    emit('assignRole', role);
}
</script>
