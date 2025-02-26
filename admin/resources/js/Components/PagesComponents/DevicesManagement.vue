<template>
    <div class="p-6 overflow-hidden rounded-md shadow-md">
        <!-- Mobile Layout: visible on screens smaller than sm -->
        <div class="sm:hidden mb-2">
            <div class="flex flex-col space-y-2">
                <div>
                    <input v-model="searchQuery" type="text" placeholder="Search by device name"
                        class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="flex space-x-2">
                    <button @click="fetchDevices(1)" :disabled="loading"
                        class="flex-1 btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        <span v-else>Search</span>
                    </button>
                    <button @click="resetSearch"
                        class="flex-1 btn text-white btn-secondary shadow-lg hover:bg-gray-400">
                        Reset
                    </button>
                </div>
                <div>
                    <button @click="openAddModal"
                        class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                        Add Device
                    </button>
                </div>
            </div>
        </div>

        <!-- Desktop Layout: visible on screens sm and larger -->
        <div class="hidden sm:block">
            <div class="flex mb-2">
                <input v-model="searchQuery" type="text" placeholder="Search by device name"
                    class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <button @click="fetchDevices(1)" :disabled="loading"
                    class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Search</span>
                </button>
                <button @click="resetSearch" class="btn text-white btn-secondary shadow-lg hover:bg-gray-400 ml-2">
                    Reset
                </button>
            </div>
            <button @click="openAddModal" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
                Add Device
            </button>
        </div>

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Device Table with Edit & Delete Actions -->
        <DaisyTable :data="devices.data" :currentPage="devices.current_page" :lastPage="devices.last_page"
            @change-page="fetchDevices" :excluded-columns="['id']">
            <!-- Assuming DaisyTable supports an "actions" slot for each row -->
            <template #actions="{ row }">
                <button @click="editDevice(row)" class="btn btn-info text-white mr-2">
                    Edit
                </button>
                <button @click="openDeleteConfirm(row.id)" class="btn btn-error text-white">
                    Delete
                </button>
            </template>
        </DaisyTable>

        <!-- Add/Edit Device Modal -->
        <DaisyModal ref="modal" :title="editMode ? 'Edit Device' : 'Add Device'">
            <template #default>
                <form @submit.prevent="saveDevice">
                    <!-- Name -->
                    <label class="label text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="deviceForm.name" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <!-- Machine ID -->
                    <label class="label text-gray-700 dark:text-gray-300">Machine ID</label>
                    <input v-model="deviceForm.machineId" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <!-- Hardware UID -->
                    <label class="label text-gray-700 dark:text-gray-300">Hardware UID</label>
                    <input v-model="deviceForm.hardwareUID" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <!-- MAC Address -->
                    <label class="label text-gray-700 dark:text-gray-300">MAC Address</label>
                    <input v-model="deviceForm.MACAdress" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <!-- Device Fingerprint -->
                    <label class="label text-gray-700 dark:text-gray-300">Device Fingerprint</label>
                    <input v-model="deviceForm.deviceFingerprint" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <div class="flex justify-center mt-4">
                        <!-- Get Info Button -->
                        <button type="button" @click="getDeviceInfo" class="btn btn-info mb-4" :disabled="gettingInfo">
                            <span v-if="gettingInfo" class="loading loading-spinner loading-sm"></span>
                            <span v-else>Get This Device Info</span>
                        </button>

                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-action">
                        <button type="button" @click="closeModal"
                            class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit"
                            class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                            <span v-if="deviceForm.processing" class="loading loading-spinner loading-sm"></span>
                            <span v-else>{{ editMode ? 'Update' : 'Save' }}</span>
                        </button>
                    </div>
                </form>
            </template>
        </DaisyModal>


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
import DaisyTable from '@/Components/DaisyTable.vue';
import DaisyModal from '@/Components/DaisyModal.vue';
import DaisyConfirm from '../DaisyConfirm.vue';

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

function openAddModal() {
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
