<template>
    <form @submit.prevent="onSubmit">
        <!-- Publisher Field -->
        <label class="label text-gray-900 dark:text-white">Publisher</label>
        <input v-model="announcementForm.publisher" type="text"
            class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" required />
        <div v-if="announcementForm.errors.publisher" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ announcementForm.errors.publisher }}
        </div>

        <!-- GENERAL Checkbox - Represents All Departments -->
        <div class="mb-2">
            <label class="label text-gray-900 dark:text-white">All Departments</label>
            <div class="flex items-center p-1">
                <input type="checkbox" value="GENERAL" v-model="selectedDepartments" id="dept-GENERAL"
                    class="ml-2 form-checkbox h-4 w-4 text-green-600" />
                <label for="dept-GENERAL" class="ml-2 text-gray-900 dark:text-white">
                    GENERAL
                </label>
            </div>
        </div>

        <!-- Other Department Checkboxes -->
        <div>
            <label class="label text-gray-900 dark:text-white">Departments</label>
            <div class="grid grid-cols-3 gap-1">
                <div v-for="dept in departments" :key="dept" class="flex items-center">
                    <input type="checkbox" :value="dept" v-model="selectedDepartments" :id="'dept-' + dept"
                        class="ml-3 form-checkbox h-4 w-4 text-green-600" />
                    <label :for="'dept-' + dept" class="ml-2 text-gray-900 dark:text-white">
                        {{ dept }}
                    </label>
                </div>
            </div>
            <div v-if="announcementForm.errors.departments" class="text-sm mb-2 text-red-500 dark:text-white">
                {{ announcementForm.errors.departments }}
            </div>
        </div>

        <!-- Optional: Display Comma-Separated String for Debug/Feedback -->
        <p class="mt-2 text-gray-700 dark:text-gray-300">
            Selected: {{ departmentsString }}
        </p>

        <!-- Publication Date -->
        <label class="label text-gray-900 dark:text-white">Publication Date</label>
        <input v-model="announcementForm.publication_date" type="date" :min="minPublicationDate"
            class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" required />
        <div v-if="announcementForm.errors.publication_date" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ announcementForm.errors.publication_date }}
        </div>

        <!-- Type Dropdown -->
        <label class="label text-gray-900 dark:text-white">Type</label>
        <select v-model="announcementForm.type"
            class="select select-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" required>
            <option disabled value="">-- Select Announcement Type --</option>
            <option value="text">Text</option>
            <option value="image">Image</option>
        </select>
        <div v-if="announcementForm.errors.type" class="text-sm mb-2 text-red-500 dark:text-white">
            {{ announcementForm.errors.type }}
        </div>

        <!-- Conditionally Render Content Fields -->
        <div v-if="announcementForm.type === 'text'">
            <!-- Text Announcement: Title -->
            <label class="label text-gray-900 dark:text-white">Title</label>
            <input v-model="extraContent.title" type="text"
                class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" required />
            <div v-if="announcementForm.errors.title" class="text-sm mb-2 text-red-500 dark:text-white">
                {{ announcementForm.errors.title }}
            </div>
            <!-- Text Announcement: Body -->
            <label class="label text-gray-900 dark:text-white">Body</label>
            <textarea v-model="extraContent.body"
                class="textarea textarea-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" required></textarea>
            <div v-if="announcementForm.errors.body" class="text-sm mb-2 text-red-500 dark:text-white">
                {{ announcementForm.errors.body }}
            </div>
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
            <button type="button" @click="onCancel" class="mr-4 hover:underline text-black dark:text-white">
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
import { ref, computed, watch } from 'vue'

// Props passed from parent component
const props = defineProps({
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

// Event emitter for communicating with the parent component
const emit = defineEmits(['cancel', 'save', 'image-upload'])

// Use a separate ref for the selected departments (as an array)
const selectedDepartments = ref([])

// Computed property to create a comma-separated string from the array.
// If "GENERAL" is selected, it returns only "GENERAL".
const departmentsString = computed(() => {
    if (selectedDepartments.value.includes('GENERAL')) {
        return 'GENERAL'
    }
    return selectedDepartments.value.join(', ')
})

// Watch for changes in selectedDepartments to enforce exclusive "GENERAL" behavior.
watch(selectedDepartments, (newVal, oldVal) => {
    if (newVal.length > oldVal.length) {
        const added = newVal.filter(x => !oldVal.includes(x))
        if (added.includes('GENERAL')) {
            selectedDepartments.value = ['GENERAL']
        } else if (oldVal.includes('GENERAL')) {
            selectedDepartments.value = newVal.filter(x => x !== 'GENERAL')
        }
    }
})

// Watch for changes in announcementForm.departments when editing
watch(() => props.announcementForm.departments, (newVal) => {
    if (newVal) {
        selectedDepartments.value = newVal.split(', ')
    }
}, { immediate: true })

function onCancel() {
    selectedDepartments.value = [] // Reset selected departments
    emit('cancel')
}

function onSubmit() {
    props.announcementForm.departments = departmentsString.value
    selectedDepartments.value = [] // Reset selected departments after saving
    emit('save')
}

function onImageUpload(event) {
    emit('image-upload', event)
}
</script>
