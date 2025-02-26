<template>
    <div class="p-6 overflow-hidden rounded-md shadow-md">
        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Device Search -->
        <DeviceSearch v-model="searchQuery" :loading="loading" @search="fetchDevices(1)" @reset="resetSearch"
            @add="openAddModal" />

        <!-- Device Table -->
        <DeviceTable :devices="devices" @edit="onEditDevice" @delete="openDeleteConfirm" @change-page="fetchDevices" />

        <!-- Device Modal -->
        <DeviceModal v-model="deviceForm" :isEditMode="editMode" :processing="deviceForm.processing"
            @submit="saveDevice" @cancel="closeModal" ref="deviceModalRef" />

        <!-- DaisyConfirm for Delete Confirmation -->
        <DaisyConfirm :visible="confirmVisible" title="Delete Device"
            message="Are you sure you want to delete this device?" @confirm="handleDeleteConfirm"
            @cancel="handleDeleteCancel" />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DeviceSearch from './DeviceSearch.vue';
import DeviceTable from './DeviceTable.vue';
import DeviceModal from './DeviceModal.vue';
import DaisyConfirm from '@/Components/DaisyConfirm.vue';

const props = defineProps({
    devices: Object,
    search: String,
});

const searchQuery = ref(props.search || "");
const loading = ref(false);
const successMessage = ref("");
const editMode = ref(false);

// Inertia form for device data
const deviceForm = useForm({
    id: null,
    name: "",
    machineId: "",
    hardwareUID: "",
    MACAdress: "",
    deviceFingerprint: ""
});

const deviceModalRef = ref(null);

function openAddModal() {
    editMode.value = false;
    deviceForm.reset();
    deviceModalRef.value.showModal();
}

function onEditDevice(device) {
    editMode.value = true;
    deviceForm.reset();
    deviceForm.id = device.id;
    deviceForm.name = device.name;
    deviceForm.machineId = device.machineId;
    deviceForm.hardwareUID = device.hardwareUID;
    deviceForm.MACAdress = device.MACAdress;
    deviceForm.deviceFingerprint = device.deviceFingerprint;
    deviceModalRef.value.showModal();
}

function saveDevice() {
    if (editMode.value) {
        deviceForm.put(`/devices/${deviceForm.id}`, {
            onSuccess: () => {
                deviceModalRef.value.closeModal();
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
        deviceForm.post('/devices', {
            onSuccess: () => {
                deviceModalRef.value.closeModal();
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

const currentPage = ref(1);
function fetchDevices(page) {
    loading.value = true;
    currentPage.value = page;
    router.get(`/devices`, { page, search: searchQuery.value }, {
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

function closeModal() {
    deviceModalRef.value.closeModal();
}
</script>
