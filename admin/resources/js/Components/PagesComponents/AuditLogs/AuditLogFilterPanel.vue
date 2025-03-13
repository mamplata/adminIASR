<template>
    <div class="w-full mb-4">
        <!-- First Row: Search Details Input -->
        <div class="flex flex-col md:flex-row gap-4">
            <input v-model="localSearchDetails" type="text" class="input input-bordered w-full md:w-auto"
                placeholder="Search Details..." />
            <FilterDropdown v-model="localSelectedAction" :options="actions" placeholder="Select Action" />
            <FilterDropdown v-model="localSelectedType" :options="types" placeholder="Select Type" />
            <FilterDropdown v-model="localSelectedAdmin" :options="admins" placeholder="Select Admin" />
        </div>

        <!-- Second Row: Date Range and Buttons -->
        <div class="flex flex-col md:flex-row items-center gap-2 mt-4">
            <DateRangePicker class="w-full" v-model:startDate="localStartDate" v-model:endDate="localEndDate" />
            <button @click="$emit('search')" class="btn btn-success text-white hover:bg-[#20714c] w-full md:w-auto">
                Search
            </button>
            <button @click="$emit('reset')" class="btn btn-neutral text-white hover:bg-[#7b7b7b] w-full md:w-auto">
                Reset
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import DateRangePicker from './DateRangePicker.vue';
import FilterDropdown from './FilterDropdown.vue';

const props = defineProps({
    selectedAction: String,
    selectedType: String,
    selectedAdmin: [String, Number],
    startDate: String,
    endDate: String,
    searchDetails: String,
    actions: Array,
    types: Array,
    admins: Array,
});

const emits = defineEmits([
    "update:selectedAction",
    "update:selectedType",
    "update:selectedAdmin",
    "update:startDate",
    "update:endDate",
    "update:searchDetails",
    "search",
    "reset"
]);

// Computed properties for two-way binding
const localSelectedAction = computed({
    get: () => props.selectedAction,
    set: (val) => emits("update:selectedAction", val)
});
const localSelectedType = computed({
    get: () => props.selectedType,
    set: (val) => emits("update:selectedType", val)
});
const localSelectedAdmin = computed({
    get: () => props.selectedAdmin,
    set: (val) => emits("update:selectedAdmin", val)
});
const localStartDate = computed({
    get: () => props.startDate,
    set: (val) => emits("update:startDate", val)
});
const localEndDate = computed({
    get: () => props.endDate,
    set: (val) => emits("update:endDate", val)
});
const localSearchDetails = computed({
    get: () => props.searchDetails,
    set: (val) => emits("update:searchDetails", val)
});
</script>
