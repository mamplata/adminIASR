<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- SearchBar Component -->
        <DeviceSearchBar v-model="searchQuery" v-model:status="status" :loading="loading" @search="fetchDevices(1)"
            @reset="resetSearch" @add-device="openModal" />

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Device Table -->
        <DeviceTable :devices="devices" @change-page="fetchDevices" @delete-device="openDeleteConfirm"
            @edit-device="openEditModal" />

        <!-- DaisyConfirm for Delete Confirmation -->
        <DaisyConfirm :visible="confirmVisible" title="Delete Device"
            message="Are you sure you want to delete this device?" @confirm="handleDeleteConfirm"
            @cancel="handleDeleteCancel" />

        <!-- DaisyModal with DeviceModal -->
        <DaisyModal ref="modalRef" :title="modalTitle">
            <template #default>
                <DeviceModal :deviceForm="deviceForm" :title="modalTitle" @save="saveDevice" @cancel="closeModal" />
            </template>
        </DaisyModal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import DaisyConfirm from '@/Components/Daisy/DaisyConfirm.vue';
import DeviceSearchBar from './DeviceSearchBar.vue';
import DeviceTable from './DeviceTable.vue';
import DaisyModal from '@/Components/Daisy/DaisyModal.vue';
import DeviceModal from './DeviceModal.vue';


const props = defineProps({
    devices: Object,
    search: String,
});

const { props: page } = usePage();

const searchQuery = ref(page.search || "");
const status = ref(page.status || "");
const currentPage = ref(1);
const loading = ref(false);
const successMessage = ref("");

// Update deviceForm to include only id, name, and status.
const deviceForm = useForm({
    id: null,
    name: "",
    status: ""
});

// Reference to the modal component and modal title
const modalRef = ref(null);
const modalTitle = ref("Add Device");

// Methods
function openModal() {
    modalTitle.value = "Add Device";
    deviceForm.reset();
    deviceForm.id = null;
    deviceForm.status = "inactive"; // Default status for new device
    modalRef.value.showModal();
}

function closeModal() {
    modalRef.value.closeModal();
}

// Save device using updateOrCreate logic on the backend via the same POST route.
// The backend checks if an id is present in the request data.
function saveDevice() {
    deviceForm.post('/devices', {
        onSuccess: () => {
            successMessage.value = deviceForm.id
                ? "Device updated successfully!"
                : "Device saved successfully!";
            setTimeout(() => (successMessage.value = ""), 4000);
            deviceForm.reset();
            fetchDevices(1);
            closeModal();
        },
        onError: () => {
            successMessage.value = deviceForm.id
                ? "Error updating device. Try again!"
                : "Error saving device. Try again!";
            setTimeout(() => (successMessage.value = ""), 4000);
        }
    });
}

// Fetch devices with search, status filter, and pagination.
function fetchDevices(page) {
    loading.value = true;
    router.get(
        `/devices`,
        { page: page, search: searchQuery.value, status: status.value },
        {
            preserveState: true,
            onFinish: () => {
                loading.value = false;
            }
        }
    );
}

function resetSearch() {
    searchQuery.value = "";
    status.value = "";
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

// Handle edit: populate the form with the whole row data.
function openEditModal(row) {
    modalTitle.value = "Edit Device";
    deviceForm.id = row.id;
    deviceForm.name = row.name;
    deviceForm.status = row.status;
    modalRef.value.showModal();
}
</script>
