<template>
    <form @submit.prevent="onSubmit">
        <!-- Publisher Field -->
        <label class="label text-gray-900 dark:text-white">Publisher</label>
        <input v-model="announcementForm.publisher" type="text"
            class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
            required />

        <!-- Department Dropdown -->
        <label class="label text-gray-900 dark:text-white">Department</label>
        <select v-model="announcementForm.department"
            class="input w-full input-bordered mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
            <option value="" disabled>Select Department</option>
            <option v-for="dept in departments" :key="dept" :value="dept">
                {{ dept }}
            </option>
        </select>

        <!-- Publication Date -->
        <label class="label text-gray-900 dark:text-white">Publication Date</label>
        <input v-model="announcementForm.publication_date" type="date" :min="minPublicationDate"
            class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
            required />

        <!-- Type Dropdown -->
        <label class="label text-gray-900 dark:text-white">Type</label>
        <select v-model="announcementForm.type"
            class="select select-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
            required>
            <option disabled value="">-- Select Announcement Type --</option>
            <option value="text">Text</option>
            <option value="image">Image</option>
        </select>

        <!-- Conditionally Render Content Fields -->
        <div v-if="announcementForm.type === 'text'">
            <!-- Text Announcement: Title -->
            <label class="label text-gray-900 dark:text-white">Title</label>
            <input v-model="extraContent.title" type="text"
                class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                required />
            <!-- Text Announcement: Body -->
            <label class="label text-gray-900 dark:text-white">Body</label>
            <textarea v-model="extraContent.body"
                class="textarea textarea-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                required></textarea>
        </div>

        <div v-else-if="announcementForm.type === 'image'">
            <!-- Image Announcement -->
            <label class="label text-gray-900 dark:text-white">{{ uploadImage }}</label>
            <div class="relative flex flex-col items-center mb-2">
                <input type="file" accept="image/*" @change="onImageUpload" class="hidden" id="fileInput"
                    :required="isFileRequired" />
                <label for="fileInput"
                    class="cursor-pointer px-4 py-2 w-full border border-gray-300 rounded-md text-center bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                    {{ fileName || 'Choose File' }}
                </label>
            </div>
        </div>

        <!-- Modal Actions -->
        <div class="modal-action">
            <button type="button" @click="onCancel"
                class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                Cancel
            </button>
            <button type="submit"
                class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                <span v-if="announcementForm.processing" class="loading loading-spinner loading-sm"></span>
                <span v-else>Save</span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    currentAction: {
        type: String,
        default: 'Announcement'
    },
    announcementForm: {
        type: Object,
        required: true
    },
    extraContent: {
        type: Object,
        required: true
    },
    departments: {
        type: Array,
        default: () => []
    },
    minPublicationDate: {
        type: String,
        default: ''
    },
    uploadImage: {
        type: String,
        default: 'Upload Image'
    },
    fileName: {
        type: String,
        default: ''
    },
    isFileRequired: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['cancel', 'save', 'image-upload'])

function onCancel() {
    emit('cancel')
}

function onSubmit() {
    emit('save')
}

function onImageUpload(event) {
    emit('image-upload', event)
}
</script>
