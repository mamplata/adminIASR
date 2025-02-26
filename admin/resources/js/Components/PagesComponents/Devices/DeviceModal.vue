<template>
    <div>
        <dialog ref="modal" class="modal">
            <div class="modal-box bg-white dark:bg-gray-800 dark:text-white">
                <!-- Modal Title -->
                <h2 class="text-lg font-bold">{{ title }}</h2>

                <!-- Device Form -->
                <form @submit.prevent="$emit('save')">
                    <!-- Name -->
                    <label class="label text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="deviceForm.name" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="deviceForm.errors.name" class="text-sm mb-2 text-red-500 dark:text-white">{{
                        deviceForm.errors.name
                        }}</div>

                    <!-- Machine ID -->
                    <label class="label text-gray-700 dark:text-gray-300">Machine ID</label>
                    <input v-model="deviceForm.machineId" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="deviceForm.errors.machineId" class="text-sm mb-2 text-red-500 dark:text-white">{{
                        deviceForm.errors.machineId
                        }}</div>

                    <!-- Hardware UID -->
                    <label class="label text-gray-700 dark:text-gray-300">Hardware UID</label>
                    <input v-model="deviceForm.hardwareUID" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="deviceForm.errors.hardwareUID" class="text-sm mb-2 text-red-500 dark:text-white">{{
                        deviceForm.errors.hardwareUID
                    }}</div>

                    <!-- MAC Address -->
                    <label class="label text-gray-700 dark:text-gray-300">MAC Address</label>
                    <input v-model="deviceForm.MACAdress" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="deviceForm.errors.MACAdress" class="text-sm mb-2 text-red-500 dark:text-white">{{
                        deviceForm.errors.MACAdress
                    }}</div>

                    <!-- Device Fingerprint -->
                    <label class="label text-gray-700 dark:text-gray-300">Device Fingerprint</label>
                    <input v-model="deviceForm.deviceFingerprint" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="deviceForm.errors.deviceFingerprint" class="text-sm mb-2 text-red-500 dark:text-white">{{
                        deviceForm.errors.deviceFingerprint
                        }}</div>

                    <div class="flex justify-center mt-4">
                        <!-- Get Info Button -->
                        <button type="button" @click="$emit('get-info')" class="btn btn-info mb-4"
                            :disabled="gettingInfo">
                            <span v-if="gettingInfo" class="loading loading-spinner loading-sm"></span>
                            <span v-else>Get This Device Info</span>
                        </button>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-action">
                        <button type="button" @click="$emit('cancel')"
                            class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit"
                            class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                            <span v-if="deviceForm.processing" class="loading loading-spinner loading-sm"></span>
                            <span v-else>{{ title === 'Edit Device' ? 'Update' : 'Save' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>
</template>

<script>
export default {
    name: 'DeviceModal',
    props: {
        title: {
            type: String,
            default: "Modal"
        },
        deviceForm: {
            type: Object,
            required: true
        },
        gettingInfo: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        showModal() {
            this.$refs.modal.showModal();
        },
        closeModal() {
            this.$refs.modal.close();
        }
    }
};
</script>
