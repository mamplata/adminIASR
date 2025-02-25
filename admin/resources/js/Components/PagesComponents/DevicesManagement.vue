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
                <button @click="deleteDevice(row.id)" class="btn btn-error text-white">
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
    </div>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';
import { useForm, router } from '@inertiajs/vue3';
import DaisyModal from '@/Components/DaisyModal.vue';

export default {
    props: { devices: Object, search: String },
    data() {
        return {
            searchQuery: this.search || "",
            currentPage: 1,
            loading: false,
            successMessage: "",
            // Flag to determine if we are editing or adding a device.
            editMode: false,
            // Form initialization for both add and edit.
            deviceForm: useForm({
                id: null,
                name: "",
                machineId: "",
                hardwareUID: "",
                MACAdress: "",
                deviceFingerprint: ""
            }),
        };
    },
    methods: {
        closeModal() {
            this.$refs.modal.closeModal();
            // Reset edit mode on modal close
            this.editMode = false;
            this.deviceForm.reset();
        },
        openAddModal() {
            // Ensure form is reset and editMode is off before adding a new device.
            this.editMode = false;
            this.deviceForm.reset();
            this.$refs.modal.showModal();
        },
        editDevice(device) {
            // Enable edit mode and pre-fill the form with the selected device data.
            this.editMode = true;
            this.deviceForm.reset();
            this.deviceForm.id = device.id;
            this.deviceForm.name = device.name;
            this.deviceForm.machineId = device.machineId;
            this.deviceForm.hardwareUID = device.hardwareUID;
            this.deviceForm.MACAdress = device.MACAdress;
            this.deviceForm.deviceFingerprint = device.deviceFingerprint;
            this.$refs.modal.showModal();
        },
        saveDevice() {
            if (this.editMode) {
                // Update existing device using a PUT request.
                this.deviceForm.put(`/devices/${this.deviceForm.id}`, {
                    onSuccess: () => {
                        this.$refs.modal.closeModal();
                        this.successMessage = "Device updated successfully!";
                        setTimeout(() => (this.successMessage = ""), 4000);
                        this.deviceForm.reset();
                        this.editMode = false;
                        this.fetchDevices(1);
                    },
                    onError: () => {
                        this.successMessage = "Error updating device. Try again!";
                    }
                });
            } else {
                // Create a new device using a POST request.
                this.deviceForm.post('/devices', {
                    onSuccess: () => {
                        this.$refs.modal.closeModal();
                        this.successMessage = "Device added successfully!";
                        setTimeout(() => (this.successMessage = ""), 4000);
                        this.deviceForm.reset();
                        this.fetchDevices(1);
                    },
                    onError: () => {
                        this.successMessage = "Error adding device. Try again!";
                    }
                });
            }
        },
        deleteDevice(id) {
            if (confirm("Are you sure you want to delete this device?")) {
                router.delete(`/devices/${id}`, {
                    onSuccess: () => {
                        this.successMessage = "Device deleted successfully!";
                        this.fetchDevices(this.currentPage);
                        setTimeout(() => (this.successMessage = ""), 4000);
                    },
                    onError: () => {
                        this.successMessage = "Error deleting device. Try again!";
                    }
                });
            }
        },
        changePage(page) {
            this.currentPage = page;
            this.fetchDevices(page);
        },
        fetchDevices(page) {
            this.loading = true;
            router.get(`/devices`, { page: page, search: this.searchQuery }, {
                preserveState: true,
                onFinish: () => {
                    this.loading = false;
                }
            });
        },
        resetSearch() {
            this.searchQuery = "";
            this.fetchDevices(1);
        },
    },
    components: {
        DaisyTable,
        DaisyModal,
    },
};
</script>
