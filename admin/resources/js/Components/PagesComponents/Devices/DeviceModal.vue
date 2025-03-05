<template>
    <form @submit.prevent="emitSave">
        <!-- Name Field -->
        <label class="label text-gray-700 dark:text-gray-300">Name</label>
        <input v-model="deviceForm.name" type="text"
            class="input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500"
            required />
        <div v-if="deviceForm.errors.name" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ deviceForm.errors.name }}
        </div>

        <!-- Status Field (only for Edit Mode) -->
        <div v-if="title === 'Edit Device'">
            <label class="label text-gray-700 dark:text-gray-300">Status</label>
            <select v-model="deviceForm.status"
                class="select select-bordered w-full mb-1 bg-white text-gray-900 rounded focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500"
                required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <div v-if="deviceForm.errors.status" class="text-sm mb-2 text-red-500 dark:text-white">
                {{ deviceForm.errors.status }}
            </div>
        </div>

        <!-- Modal Actions -->
        <div class="modal-action mt-4">
            <button type="button" @click="$emit('cancel')" class="mr-4 hover:underline text-black dark:text-white">
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
import { defineProps, defineEmits, onMounted } from 'vue'

const props = defineProps({
    deviceForm: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        default: 'Add Device'
    }
})

const emit = defineEmits(['cancel', 'save'])

function emitSave() {
    emit('save')
}

// For new devices, ensure status is set to 'inactive'
onMounted(() => {
    if (props.title !== 'Edit Device') {
        props.deviceForm.status = 'inactive'
    }
})
</script>
