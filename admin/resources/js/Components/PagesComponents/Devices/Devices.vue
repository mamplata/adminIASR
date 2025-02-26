<template>
    <div class="p-6 overflow-hidden rounded-md shadow-md">

        <!-- SearchBar Component -->
        <DeviceSearchBar v-model="searchQuery" :loading="loading" @search="fetchDevices(1)" @reset="resetSearch()"
            @add-device="openModal" />

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Device Table -->
        <DeviceTable :devices="devices" @change-page="fetchDevices" @edit-device="editDevice"
            @delete-device="openDeleteConfirm" />

        <!-- Add/Edit Device Modal (Using DeviceModal) -->
        <DeviceModal ref="modal" :title="editMode ? 'Edit Device' : 'Add Device'" :deviceForm="deviceForm"
            :gettingInfo="gettingInfo" @cancel="closeModal" @save="saveDevice" @get-info="getDeviceInfo" />


        <!-- DaisyConfirm for Delete Confirmation -->
        <DaisyConfirm :visible="confirmVisible" title="Delete Device"
            message="Are you sure you want to delete this device?" @confirm="handleDeleteConfirm"
            @cancel="handleDeleteCancel" />
    </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { io } from 'socket.io-client';
import DaisyConfirm from '@/Components//DaisyConfirm.vue';
import DeviceSearchBar from './DeviceSearchBar.vue';
import DeviceTable from './DeviceTable.vue';
import DeviceModal from './DeviceModal.vue';

// Define props
const props = defineProps({
    devices: Object,
    search: String,
});

// Refs and reactive state
const searchQuery = ref(props.search || "");
const currentPage = ref(1);
const loading = ref(false);
const successMessage = ref("");
const editMode = ref(false);
const gettingInfo = ref(false);

// Reference to the modal component
const modal = ref(null);

// Initialize the form using Inertia's useForm
const deviceForm = useForm({
    id: null,
    name: "",
    machineId: "",
    hardwareUID: "",
    MACAdress: "",
    deviceFingerprint: ""
});

// Initialize the socket connection
const socket = io("http://localhost:3000");

// Function to trigger the getDeviceInfo event
function getDeviceInfo() {
    gettingInfo.value = true;
    socket.emit("getDeviceInfo");
}

// Listen for the response from the server
socket.on("deviceInfo", (details) => {
    // Map server details to form fields
    deviceForm.name = details.deviceName;
    deviceForm.machineId = details.machineId;
    deviceForm.hardwareUID = details.hardwareUUID;
    deviceForm.MACAdress = details.macAddress;
    deviceForm.deviceFingerprint = details.deviceFingerprint;
    gettingInfo.value = false;
});

// Listen for any errors from the server
socket.on("error", (errorMessage) => {
    successMessage.value = errorMessage;
    gettingInfo.value = false;
});

// Clean up the socket connection when the component is unmounted
onUnmounted(() => {
    socket.disconnect();
});

// Functions
function closeModal() {
    modal.value.closeModal();
    editMode.value = false;
    deviceForm.reset();
}

function openModal() {
    editMode.value = false;
    deviceForm.reset();
    modal.value.showModal();
}

function editDevice(device) {
    editMode.value = true;
    deviceForm.reset();
    deviceForm.id = device.id;
    deviceForm.name = device.name;
    deviceForm.machineId = device.machineId;
    deviceForm.hardwareUID = device.hardwareUID;
    deviceForm.MACAdress = device.MACAdress;
    deviceForm.deviceFingerprint = device.deviceFingerprint;
    modal.value.showModal();
}

function saveDevice() {
    if (editMode.value) {
        // Update existing device using a PUT request.
        deviceForm.put(`/devices/${deviceForm.id}`, {
            onSuccess: () => {
                modal.value.closeModal();
                successMessage.value = "Device updated successfully!";
                setTimeout(() => (successMessage.value = ""), 4000);
                deviceForm.reset();
                editMode.value = false;
                fetchDevices(1);
            },
            onError: () => {
                successMessage.value = "Error updating device. Try again!";
            }
        });
    } else {
        // Create a new device using a POST request.
        deviceForm.post('/devices', {
            onSuccess: () => {
                modal.value.closeModal();
                successMessage.value = "Device added successfully!";
                setTimeout(() => (successMessage.value = ""), 4000);
                deviceForm.reset();
                fetchDevices(1);
            },
            onError: () => {
                successMessage.value = "Error adding device. Try again!";
            }
        });
    }
}

function deleteDevice(id) {
    if (confirm("Are you sure you want to delete this device?")) {
        router.delete(`/devices/${id}`, {
            onSuccess: () => {
                successMessage.value = "Device deleted successfully!";
                fetchDevices(currentPage.value);
                setTimeout(() => (successMessage.value = ""), 4000);
            },
            onError: () => {
                successMessage.value = "Error deleting device. Try again!";
            }
        });
    }
}

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

// For delete confirmation
const confirmVisible = ref(false);
const deleteId = ref(null);

// Instead of using confirm(), open the confirmation modal
function openDeleteConfirm(id) {
    deleteId.value = id;
    confirmVisible.value = true;
}

// Handle confirmation from DaisyConfirm
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
