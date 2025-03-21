<template>
    <div>
        <!-- Modal for port status only -->
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
                <div class="modal-action mt-4">
                    <button @click="closeModal" class="btn">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getSocket } from '@/composables/socket';
const emit = defineEmits(['close']);

const timeInInfo = ref(null);
const timeOutInfo = ref(null);

let socket = null;

const props = defineProps({
    deviceName: { type: String, required: true },
});

onMounted(() => {
    socket = getSocket();
    if (!socket) {
        console.error('Socket connection is not initialized. Make sure to call initializeSocket() in a parent or registration component.');
        return;
    }

    socket.on("scannerDetected", (data) => {
        if (data.assigned) {
            if (data.role === "Time In") {
                timeInInfo.value = data;
            } else if (data.role === "Time Out") {
                timeOutInfo.value = data;
            }
        }
    });

    socket.on("scannerAssigned", (data) => {
        let updatedInfo;
        if (data.role === "Time In") {
            updatedInfo = { ...data, online: true, role: "Time In" };
            timeInInfo.value = updatedInfo;
        } else if (data.role === "Time Out") {
            updatedInfo = { ...data, online: true, role: "Time Out" };
            timeOutInfo.value = updatedInfo;
        }
    });
});

function closeModal() {
    emit('close');
}
</script>
