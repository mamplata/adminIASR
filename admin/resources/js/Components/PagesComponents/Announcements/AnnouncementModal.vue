<template>
    <div ref="container" tabindex="0" class="max-w-5xl mx-auto px-4 py-6" @keydown.enter="handleEnter">
        <form ref="form" @submit.prevent="onSubmit">
            <div class="grid grid-cols-2 gap-8">
                <!-- Left Column -->
                <div>
                    <!-- Publisher Field -->
                    <label class="label text-gray-900 dark:text-white">Publisher</label>
                    <input v-model="announcementForm.publisher" type="text"
                        class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="announcementForm.errors.publisher" class="text-sm mb-2 text-red-500 dark:text-red-500">
                        {{ announcementForm.errors.publisher }}
                    </div>


                    <!-- Publication Date -->
                    <label class="label text-gray-900 dark:text-white">Publication Date</label>
                    <input v-model="announcementForm.publication_date" type="date" :min="minPublicationDate"
                        class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="announcementForm.errors.publication_date"
                        class="text-sm mb-2 text-red-500 dark:text-red-500">
                        {{ announcementForm.errors.publication_date }}
                    </div>

                    <!-- End Date of Announcement -->
                    <label class="label text-gray-900 dark:text-white">End Date</label>
                    <input v-model="announcementForm.end_date" type="date" :min="announcementForm.publication_date"
                        class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />
                    <div v-if="announcementForm.errors.end_date" class="text-sm mb-2 text-red-500 dark:text-red-500">
                        {{ announcementForm.errors.end_date }}
                    </div>

                    <!-- Type Dropdown -->
                    <label class="label text-gray-900 dark:text-white">Type</label>
                    <select v-model="announcementForm.type"
                        class="select select-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required>
                        <option disabled value="">-- Select Announcement Type --</option>
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                    </select>
                    <div v-if="announcementForm.errors.type" class="text-sm mb-2 text-red-500 dark:text-red-500">
                        {{ announcementForm.errors.type }}
                    </div>

                    <!-- Conditionally Render Content Fields -->
                    <div v-if="announcementForm.type === 'text'">
                        <!-- Text Announcement: Title -->
                        <label class="label text-gray-900 dark:text-white">Title</label>
                        <input v-model="extraContent.title" type="text"
                            class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                            required />
                        <div v-if="announcementForm.errors['content.title']"
                            class="text-sm mb-2 text-red-500 dark:text-red-500">
                            {{ announcementForm.errors['content.title'] }}
                        </div>

                        <!-- Text Announcement: Body -->
                        <label class="label text-gray-900 dark:text-white">Body</label>
                        <textarea rows="10" v-model="extraContent.body"
                            class="textarea textarea-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                            required></textarea>
                        <div v-if="announcementForm.errors.body" class="text-sm mb-2 text-red-500 dark:text-white">
                            {{ announcementForm.errors.body }}
                        </div>
                    </div>

                    <div v-else-if="announcementForm.type === 'image'">
                        <!-- Image Announcement -->
                        <label class="label text-gray-900 dark:text-white">{{ uploadImage }}</label>
                        <div class="relative flex flex-col items-center mb-2">
                            <input name="fileInput" type="file" accept="image/*" @change="onImageUpload"
                                class="visually-hidden" id="fileInput" :required="isFileRequired" />
                            <label for="fileInput"
                                class="cursor-pointer px-4 py-2 w-full border border-gray-300 rounded-md text-center bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                                {{ fileName || 'Choose File' }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>

                    <!-- GENERAL Checkbox - Represents All Departments -->
                    <div class="mb-2">
                        <label class="label text-gray-900 dark:text-white">All Departments</label>
                        <div class="flex items-center p-1">
                            <input type="checkbox" value="GENERAL" v-model="selectedDepartments" id="dept-GENERAL"
                                class="ml-2 form-checkbox h-4 w-4 text-green-600" />
                            <label for="dept-GENERAL" class="ml-2 text-gray-900 dark:text-white">GENERAL</label>
                        </div>
                    </div>

                    <!-- Other Department Checkboxes -->
                    <div>
                        <label class="label text-gray-900 dark:text-white">Departments</label>
                        <div class="grid grid-cols-3 gap-1">
                            <div v-for="dept in departments" :key="dept" class="flex items-center">
                                <input type="checkbox" :value="dept" v-model="selectedDepartments" :id="'dept-' + dept"
                                    class="ml-3 form-checkbox h-4 w-4 text-green-600" />
                                <label :for="'dept-' + dept" class="ml-2 text-gray-900 dark:text-white">{{ dept
                                }}</label>
                            </div>
                        </div>
                        <div v-if="announcementForm.errors.departments"
                            class="text-sm mb-2 text-red-500 dark:text-red-500">
                            {{ announcementForm.errors.departments }}
                        </div>
                    </div>

                    <!-- Display Selected Departments (for debugging/feedback) -->
                    <p class="mt-2 text-gray-700 dark:text-gray-300">
                        Selected Departments: {{ departmentsString }}
                    </p>
                    <div v-for="dept in filteredDepartments" :key="dept">
                        <label class="label text-gray-900 dark:text-white">{{ dept }} Programs</label>
                        <multiselect v-model="selectedDepartmentPrograms[dept]" :options="getProgramOptions(dept)"
                            :multiple="true" group-values="libs" group-label="language" :group-select="true"
                            placeholder="Select programs" track-by="name" label="name" required></multiselect>
                    </div>

                </div>

                <!-- Modal Actions (spanning both columns) -->
                <div class="col-span-2 flex justify-end mt-4">
                    <button type="button" @click="onCancel" class="mr-4 hover:underline text-black dark:text-white">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                        <span v-if="announcementForm.processing" class="loading loading-spinner loading-sm"></span>
                        <span v-else>Save</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>


<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

const props = defineProps({
    announcementForm: { type: Object, required: true },
    extraContent: { type: Object, required: true },
    departments: { type: Array, default: () => [] },
    departmentPrograms: { type: Object, default: () => ({}) },
    minPublicationDate: { type: String, default: '' },
    uploadImage: { type: String, default: 'Upload Image' },
    fileName: { type: String, default: '' },
    isFileRequired: { type: Boolean, default: false }
})

const emit = defineEmits(['cancel', 'save', 'image-upload'])

// References for the container and form
const container = ref(null)
const form = ref(null)

onMounted(() => {
    if (container.value) {
        container.value.focus()
    }
})

// Array of selected department names.
const selectedDepartments = ref([])

// Computed string for feedback.
const departmentsString = computed(() => {
    if (selectedDepartments.value.includes('GENERAL')) {
        return 'GENERAL'
    }
    return selectedDepartments.value.join(', ')
})

// Object to store the selected program options for each department.
// For each department key, the value is a flat array of objects (each with a "name" property).
const selectedDepartmentPrograms = ref({})

// Watcher to enforce exclusive GENERAL behavior and to initialize/remove entries.
watch(selectedDepartments, (newVal, oldVal) => {
    if (newVal.length > oldVal.length) {
        const added = newVal.filter(x => !oldVal.includes(x))
        if (added.includes('GENERAL')) {
            selectedDepartments.value = ['GENERAL']
        } else if (oldVal.includes('GENERAL')) {
            selectedDepartments.value = newVal.filter(x => x !== 'GENERAL')
        }
    }
    newVal.forEach(dept => {
        if (dept !== 'GENERAL' && !(dept in selectedDepartmentPrograms.value)) {
            selectedDepartmentPrograms.value[dept] = []
        }
    })
    Object.keys(selectedDepartmentPrograms.value).forEach(dept => {
        if (!newVal.includes(dept)) {
            delete selectedDepartmentPrograms.value[dept]
        }
    })
})

const filteredDepartments = computed(() => {
    return selectedDepartments.value.filter(dept => dept !== 'GENERAL');
})

// Compute final mapping of department to selected program names.
const finalDepartmentMapping = computed(() => {
    const mapping = {}
    selectedDepartments.value.forEach(dept => {
        if (dept === 'GENERAL') {
            mapping[dept] = ['GENERAL']
        } else {
            mapping[dept] = (selectedDepartmentPrograms.value[dept] || []).map(item => item.name)
        }
    })
    return mapping
})

// Returns options for vue-multiselect as a grouped array.
// Here, the group label is "Programs" and the options are built from departmentPrograms.
function getProgramOptions(dept) {
    const programs = props.departmentPrograms[dept] || []
    const programOptions = programs.map(prog => ({ name: prog }))
    return [
        {
            language: 'Select All',
            libs: programOptions
        }
    ]
}

function onCancel() {
    selectedDepartments.value = []
    emit('cancel')
}

function onSubmit() {
    const departmentString = Object.entries(finalDepartmentMapping.value)
        .map(([key, departments]) => {
            // If the departments array contains only "GENERAL", return just "GENERAL"
            if (departments.length === 1 && departments[0] === "GENERAL") {
                return "GENERAL";
            }
            // Otherwise, include the key and joined departments
            return `${key}: ${departments.join(", ")}`;
        })
        .join("; ");
    console.log(departmentString);
    props.announcementForm.departments = departmentString;
    selectedDepartments.value = []
    emit('save')
    props.announcementForm.departments = ''
}

function onImageUpload(event) {
    emit('image-upload', event)
}
// Function to parse the department string and update selectedDepartments and selectedDepartmentPrograms
function parseDepartmentString(deptString) {
    // Clear previous selections
    selectedDepartments.value = []
    selectedDepartmentPrograms.value = {}

    // Split by semicolon in case there are multiple entries.
    const entries = deptString
        .split(';')
        .map(e => e.trim())
        .filter(e => e.length)
    entries.forEach(entry => {
        // Handle the case where it's just "GENERAL"
        if (entry === "GENERAL") {
            if (!selectedDepartments.value.includes("GENERAL")) {
                selectedDepartments.value.push("GENERAL")
            }
        } else {
            // Assume the format is "Department: Program1, Program2"
            const parts = entry.split(':').map(part => part.trim())
            if (parts.length === 2) {
                const dept = parts[0]
                const programs = parts[1]
                    .split(',')
                    .map(p => p.trim())
                    .filter(p => p.length)
                if (dept && programs.length) {
                    if (!selectedDepartments.value.includes(dept)) {
                        selectedDepartments.value.push(dept)
                    }
                    selectedDepartmentPrograms.value[dept] = programs.map(name => ({ name }))
                }
            }
        }
    })
}

function handleEnter(event) {
    // Allow Enter in a textarea (for newlines) and any other specific exceptions
    if (event.target.tagName === 'TEXTAREA') return
}

// Watch the announcementForm.departments property to trigger parsing whenever its value changes.
watch(() => props.announcementForm.departments, (newVal) => {
    if (newVal) {
        parseDepartmentString(newVal)
    }
})
</script>

<style scoped>
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
