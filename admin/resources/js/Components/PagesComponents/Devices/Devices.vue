<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- SearchBar Component -->
        <DeviceSearchBar v-model="searchQuery" :loading="loading" @search="fetchDevices(1)" @reset="resetSearch"
            @add-device="getDeviceInfo" />

        <!-- Loading Animation -->
        <div v-if="gettingInfo" class="loading-container my-4">
            <div class="loading-spinner"></div>
            <p>Processing registration...</p>
        </div>

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Device Table -->
        <DeviceTable :devices="devices" @change-page="fetchDevices" @delete-device="openDeleteConfirm" />

        <!-- DaisyConfirm for Delete Confirmation -->
        <DaisyConfirm :visible="confirmVisible" title="Delete Device"
            message="Are you sure you want to delete this device?" @confirm="handleDeleteConfirm"
            @cancel="handleDeleteCancel" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { io } from 'socket.io-client';
import DaisyConfirm from '@/Components/DaisyConfirm.vue';
import DeviceSearchBar from './DeviceSearchBar.vue';
import DeviceTable from './DeviceTable.vue';

const props = defineProps({
    devices: Object,
    search: String,
});

const searchQuery = ref(props.search || "");
const currentPage = ref(1);
const loading = ref(false);
const successMessage = ref("");
const gettingInfo = ref(false);

// Use Inertia's useForm for the device data.
const deviceForm = useForm({
    name: "",
    machineId: "",
    hardwareUID: "",
    MACAdress: "",
    deviceFingerprint: ""
});

let socket = null;

onMounted(() => {
    // Initialize socket connection when component is mounted.
    socket = io("localhost:3000");

    socket.on("connect", () => {
        console.log("Connected to Socket.io server");
    });

    socket.on("deviceInfo", (details) => {
        deviceForm.name = details.deviceName;
        deviceForm.machineId = details.machineId;
        deviceForm.hardwareUID = details.hardwareUUID;
        deviceForm.MACAdress = details.macAddress;
        deviceForm.deviceFingerprint = details.deviceFingerprint;
        gettingInfo.value = false;
        saveDevice();
    });

    socket.on("error", (errorMessage) => {
        successMessage.value = errorMessage;
        gettingInfo.value = false;
    });

    socket.on("disconnect", () => {
        console.log("Disconnected from server");
    });
});

onUnmounted(() => {
    // Clean up the socket connection when the component unmounts.
    if (socket) {
        socket.disconnect();
    }
});

// Function to trigger the getDeviceInfo event via socket.
function getDeviceInfo() {
    gettingInfo.value = true;
    if (socket) {
        socket.emit("getDeviceInfo");
    }
}

// Save device by sending a POST request to the /devices endpoint.
function saveDevice() {
    deviceForm.post('/devices', {
        onSuccess: () => {
            successMessage.value = "Device saved successfully!";
            setTimeout(() => (successMessage.value = ""), 4000);
            deviceForm.reset();
            fetchDevices(1);
        },
        onError: (errors) => {
            if (errors.machineId || errors.hardwareUID) {
                successMessage.value = "Device Already Exist";
            } else {
                successMessage.value = "Error saving device. Try again!";
            }
            setTimeout(() => (successMessage.value = ""), 4000);
        }
    });
}

// Fetch devices with search and pagination.
function fetchDevices(page) {
    loading.value = true;
    router.get(`/devices`, { page: page, search: searchQuery.value }, {
        preserveState: true,
        onFinish: () => {
            loading.value = false;
        }
    });
}

function resetSearch() {
    searchQuery.value = "";
    fetchDevices(1);
}

// Delete confirmation handling.
const confirmVisible = ref(false);
const deleteId = ref(null);

function openDeleteConfirm(id) {
    deleteId.value = id;
    confirmVisible.value = true;
}

function handleDeleteConfirm() {
    router.delete(`/devices/${deleteId.value}`, {
        onSuccess: () => {
            successMessage.value = "Device deleted successfully!";
            fetchDevices(currentPage.value);
            setTimeout(() => (successMessage.value = ""), 4000);
        },
        onError: () => {
            successMessage.value = "Error deleting device. Try again!";
        }
    });
    confirmVisible.value = false;
    deleteId.value = null;
}

function handleDeleteCancel() {
    confirmVisible.value = false;
    deleteId.value = null;
}
</script>


<style scoped>
.loading-container {
    text-align: center;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 5px solid #ccc;
    border-top: 5px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
