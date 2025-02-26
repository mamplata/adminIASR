<template>
    <form @submit.prevent="emitSave">
        <!-- Modal Title -->
        <h2 class="text-lg font-bold">{{ title }}</h2>

        <!-- Name Field -->
        <label class="label text-gray-700 dark:text-gray-300">Name</label>
        <input v-model="deviceForm.name" type="text" :class="[
            'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
            deviceForm.errors.name ? 'border-red-500' : 'border-gray-500'
        ]" required />
        <div v-if="deviceForm.errors.name" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ deviceForm.errors.name }}
        </div>

        <!-- Machine ID Field -->
        <label class="label text-gray-700 dark:text-gray-300">Machine ID</label>
        <input v-model="deviceForm.machineId" type="text" :class="[
            'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
            deviceForm.errors.machineId ? 'border-red-500' : 'border-gray-500'
        ]" required />
        <div v-if="deviceForm.errors.machineId" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ deviceForm.errors.machineId }}
        </div>

        <!-- Hardware UID Field -->
        <label class="label text-gray-700 dark:text-gray-300">Hardware UID</label>
        <input v-model="deviceForm.hardwareUID" type="text" :class="[
            'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
            deviceForm.errors.hardwareUID ? 'border-red-500' : 'border-gray-500'
        ]" required />
        <div v-if="deviceForm.errors.hardwareUID" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ deviceForm.errors.hardwareUID }}
        </div>

        <!-- MAC Address Field -->
        <label class="label text-gray-700 dark:text-gray-300">MAC Address</label>
        <input v-model="deviceForm.MACAdress" type="text" :class="[
            'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
            deviceForm.errors.MACAdress ? 'border-red-500' : 'border-gray-500'
        ]" required />
        <div v-if="deviceForm.errors.MACAdress" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ deviceForm.errors.MACAdress }}
        </div>

        <!-- Device Fingerprint Field -->
        <label class="label text-gray-700 dark:text-gray-300">Device Fingerprint</label>
        <input v-model="deviceForm.deviceFingerprint" type="text" :class="[
            'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
            deviceForm.errors.deviceFingerprint ? 'border-red-500' : 'border-gray-500'
        ]" required />
        <div v-if="deviceForm.errors.deviceFingerprint" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ deviceForm.errors.deviceFingerprint }}
        </div>

        <!-- Get Device Info Button -->
        <div class="flex justify-center mt-4">
            <button type="button" @click="$emit('get-info')" class="btn btn-info mb-4" :disabled="gettingInfo">
                <span v-if="gettingInfo" class="loading loading-spinner loading-sm"></span>
                <span v-else>Get This Device Info</span>
            </button>
        </div>

        <!-- Modal Actions -->
        <div class="modal-action mt-4">
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
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Modal'
    },
    deviceForm: {
        type: Object,
        required: true
    },
    gettingInfo: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['cancel', 'save', 'get-info'])

function emitSave() {
    emit('save')
}
</script>
