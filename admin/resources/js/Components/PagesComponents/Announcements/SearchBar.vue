<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- First Row: Search by Publisher & Department -->
        <div class="flex flex-col md:flex-row gap-2 mb-4">
            <!-- Search by Publisher -->
            <input v-model="localSearchQuery" type="text" placeholder="Search publisher"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
               focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- Department Dropdown -->
            <select v-model="localSelectedDepartment"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
               focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
                <option value="" disabled>Select Department</option>
                <option v-for="department in searchDepartments" :key="department" :value="department">
                    {{ department }}
                </option>
            </select>
        </div>

        <!-- Second Row: Date Range and Buttons -->
        <div class="flex flex-col md:flex-row items-center gap-2 mb-4">
            <label class="whitespace-nowrap">Range Date:</label>
            <!-- Start Date with min and max -->
            <input v-model="localStartDate" type="date" :max="maxStartDate"
                class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
               focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- End Date with min and max -->
            <input v-model="localEndDate" type="date" :max="maxEndDate"
                class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
               focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- Flex Container for Search & Reset Buttons -->
            <div class="flex space-x-2 w-full md:w-auto">
                <button @click="onSearch" :disabled="loading"
                    class="flex-1 btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Search</span>
                </button>
                <button @click="onReset" class="flex-1 btn text-white btn-secondary shadow-lg hover:bg-[#7b7b7b]">
                    Reset
                </button>
            </div>
        </div>

        <!-- Add Announcement Button -->
        <button @click="onAdd" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
            Add Announcement
        </button>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

// Define props using names that match the parent's usage.
const props = defineProps({
    searchQuery: { type: String, default: '' },
    selectedDepartment: { type: String, default: '' },
    searchDepartments: { type: Array, default: () => [] },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' },
    maxStartDate: { type: String, required: true },
    maxEndDate: { type: String, required: true },
    loading: { type: Boolean, default: false },
});

// Define emits for updating parent values and handling button clicks.
const emit = defineEmits([
    'update:searchQuery',
    'update:selectedDepartment',
    'update:startDate',
    'update:endDate',
    'search',
    'reset',
    'add',
]);

// Create local refs for v-model binding.
const localSearchQuery = ref(props.searchQuery);
const localSelectedDepartment = ref(props.selectedDepartment);
const localStartDate = ref(props.startDate);
const localEndDate = ref(props.endDate);

// Watch local values and emit updates to the parent.
watch(localSearchQuery, (newVal) => {
    emit('update:searchQuery', newVal);
});
watch(localSelectedDepartment, (newVal) => {
    emit('update:selectedDepartment', newVal);
});
watch(localStartDate, (newVal) => {
    emit('update:startDate', newVal);
});
watch(localEndDate, (newVal) => {
    emit('update:endDate', newVal);
});

// Button event methods.
function onSearch() {
    // Emit a search event with page number 1.
    emit('search', 1);
}
function onReset() {
    emit('reset');
}
function onAdd() {
    emit('add');
}
</script>
