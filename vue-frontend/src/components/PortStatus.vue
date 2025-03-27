<template>
    <div>
        <!-- Overlay -->
        <div class="fixed inset-0 z-[9998] bg-black opacity-50"></div>

        <!-- Custom Port Status Modal -->
        <div
            class="fixed z-[9999] top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-base-200 border border-base-300 p-12 pb-5 shadow-lg max-w-md w-full rounded-lg">
            <h3 class="font-bold text-lg">Port and Device Status</h3>
            <p class="py-2"><strong>Device:</strong> {{ deviceStore.deviceName }}</p>
            <div>
                <p>
                    <strong>Time In: </strong>
                    <span>
                        <template v-if="scannerPortStore.timeInInfo">
                            <template v-if="scannerPortStore.timeInInfo.online">
                                <i class="fas fa-check text-green-500 mr-1"></i>
                                Online - Port {{ scannerPortStore.timeInInfo.portPath }}
                            </template>
                            <template v-else>
                                <i class="fas fa-times text-red-500 mr-1"></i>
                                Offline - Port {{ scannerPortStore.timeInInfo.portPath }}
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
                        <template v-if="scannerPortStore.timeOutInfo">
                            <template v-if="scannerPortStore.timeOutInfo.online">
                                <i class="fas fa-check text-green-500 mr-1"></i>
                                Online - Port {{ scannerPortStore.timeOutInfo.portPath }}
                            </template>
                            <template v-else>
                                <i class="fas fa-times text-red-500 mr-1"></i>
                                Offline - Port {{ scannerPortStore.timeOutInfo.portPath }}
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
                <button @click="scannerPortStore.closePortStatusModal()" class="btn">Close</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";
import { useDeviceStore } from "@/stores/deviceStore";

const scannerPortStore = useScannerPortStore();
const deviceStore = useDeviceStore();

onMounted(() => {
    if (!scannerPortStore.socket) {
        scannerPortStore.initializeSocket();
    }
});
</script>