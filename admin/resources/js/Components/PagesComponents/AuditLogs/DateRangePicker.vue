<template>
    <div class="flex flex-col md:flex-row items-center gap-2">
        <label class="whitespace-nowrap">Range Date:</label>
        <input v-model="internalStartDate" type="date" :min="formattedMinDate"
            :max="internalEndDate || formattedMaxDate"
            class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />
        <input v-model="internalEndDate" type="date" :min="internalStartDate || formattedMinDate"
            :max="formattedMaxDate"
            class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    startDate: String,
    endDate: String,
    minDate: String,  // e.g., "2025-03-08 13:51:13"    
    maxDate: String   // e.g., "2025-03-13 08:30:40"
});
const emits = defineEmits(["update:startDate", "update:endDate"]);

// If the date string is in "YYYY-MM-DD HH:MM:SS" format, simply split by space.
function formatLocalDate(dateStr) {
    if (!dateStr) return '';
    if (dateStr.includes(' ')) {
        return dateStr.split(' ')[0]; // returns "YYYY-MM-DD"
    }
    // If not, fallback to normal date parsing
    const date = new Date(dateStr);
    const yyyy = date.getFullYear();
    const mm = (date.getMonth() + 1).toString().padStart(2, '0');
    const dd = date.getDate().toString().padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

const formattedMinDate = computed(() => formatLocalDate(props.minDate));
const formattedMaxDate = computed(() => formatLocalDate(props.maxDate));

const internalStartDate = computed({
    get() {
        return props.startDate || formattedMinDate.value;
    },
    set(val) {
        emits("update:startDate", val);
    }
});

const internalEndDate = computed({
    get() {
        return props.endDate || formattedMaxDate.value;
    },
    set(val) {
        emits("update:endDate", val);
    }
});
</script>
