<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- First Row: Search by Publisher & Department -->
        <div class="flex flex-col md:flex-row gap-2 mb-4">
            <!-- Search by Publisher -->
            <input v-model="computedSearchQuery" type="text" placeholder="Search publisher"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- Department Dropdown -->
            <select v-model="computedSelectedDepartment"
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
            <input v-model="computedStartDate" type="date" :max="maxStartDate"
                class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- End Date with min and max -->
            <input v-model="computedEndDate" type="date" :max="maxEndDate"
                class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- Flex Container for Search & Reset Buttons -->
            <div class="flex space-x-2 w-full md:w-auto">
                <button @click="onSearch" :disabled="loading"
                    class="flex-1 btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Search</span>
                </button>
                <button @click="onReset" class="flex-1 btn text-dark btn-neutral shadow-lg hover:bg-[#7b7b7b]">
                    Reset
                </button>
            </div>
        </div>

        <!-- Add Announcement Button -->
        <button @click="onAdd" class="btn sm:w-full md:w-auto text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
            Add Announcement
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue';

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

// Computed properties for two-way binding.
const computedSearchQuery = computed({
    get: () => props.searchQuery,
    set: (value) => emit('update:searchQuery', value),
});

const computedSelectedDepartment = computed({
    get: () => props.selectedDepartment,
    set: (value) => emit('update:selectedDepartment', value),
});

const computedStartDate = computed({
    get: () => props.startDate,
    set: (value) => emit('update:startDate', value),
});

const computedEndDate = computed({
    get: () => props.endDate,
    set: (value) => emit('update:endDate', value),
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
