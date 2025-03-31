<template>
    <div class="w-full mb-4">
        <!-- First Row: Student ID, Time Type & Status -->
        <div class="flex flex-col md:flex-row gap-4">
            <input v-model="localSearch" type="text" class="input input-bordered w-full md:w-auto"
                placeholder="Search Student ID" />
            <FilterDropdown v-model="localTimeType" :options="timeOptions" placeholder="Select Time Type" />
            <FilterDropdown v-model="localStatus" :options="statusOptions" placeholder="Select Status" />
        </div>

        <!-- Second Row: Date Range and Buttons -->
        <div class="flex flex-col md:flex-row items-center gap-2 mt-4">
            <DateRangePicker class="w-full" v-model:startDate="localDateFrom" v-model:endDate="localDateTo" />
            <button @click="$emit('search')" class="btn btn-success text-white hover:bg-[#20714c] w-full md:w-auto">
                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Search</span>
            </button>
            <button @click="$emit('reset')" class="btn btn-neutral text-white hover:bg-[#7b7b7b] w-full md:w-auto">
                Reset
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import FilterDropdown from '../AuditLogs/FilterDropdown.vue';
import DateRangePicker from '../AuditLogs/DateRangePicker.vue';

const props = defineProps({
    search: String,
    timeType: String,
    status: String,
    dateFrom: String,
    dateTo: String,
    loading: { type: Boolean, default: false },
});

const emits = defineEmits([
    'update:search',
    'update:timeType',
    'update:status',
    'update:dateFrom',
    'update:dateTo',
    'search',
    'reset'
]);

const localSearch = computed({
    get: () => props.search,
    set: (val) => emits('update:search', val)
});
const localTimeType = computed({
    get: () => props.timeType,
    set: (val) => emits('update:timeType', val)
});
const localStatus = computed({
    get: () => props.status,
    set: (val) => emits('update:status', val)
});
const localDateFrom = computed({
    get: () => props.dateFrom,
    set: (val) => emits('update:dateFrom', val)
});
const localDateTo = computed({
    get: () => props.dateTo,
    set: (val) => emits('update:dateTo', val)
});

// Define options for the dropdowns using objects.
// The FilterDropdown component uses the object's 'id' for value and 'name' for the label.
const timeOptions = [
    { id: "IN", name: "IN" },
    { id: "OUT", name: "OUT" }
];

const statusOptions = [
    { id: "Success", name: "Success" },
    { id: "Failure", name: "Failure" }
];
</script>
