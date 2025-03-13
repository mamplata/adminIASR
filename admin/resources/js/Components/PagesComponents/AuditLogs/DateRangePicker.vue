<template>
    <div class="flex flex-col md:flex-row items-center gap-2">
        <label class="whitespace-nowrap">Range Date:</label>
        <input v-model="internalStartDate" type="date" :max="maxStartDate"
            class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />
        <input v-model="internalEndDate" type="date" :max="maxEndDate"
            class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({
    startDate: String,
    endDate: String
});
const emits = defineEmits(["update:startDate", "update:endDate"]);

const internalStartDate = computed({
    get() {
        return props.startDate;
    },
    set(val) {
        emits("update:startDate", val);
    }
});

const internalEndDate = computed({
    get() {
        return props.endDate;
    },
    set(val) {
        emits("update:endDate", val);
    }
});

// Compute maxStartDate based on internalEndDate or today's date
const maxStartDate = computed(() => {
    if (internalEndDate.value) return internalEndDate.value;
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();
    if (mm < 10) mm = `0${mm}`;
    if (dd < 10) dd = `0${dd}`;
    return `${yyyy}-${mm}-${dd}`;
});

// Compute maxEndDate as today's date
const maxEndDate = computed(() => {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();
    if (mm < 10) mm = `0${mm}`;
    if (dd < 10) dd = `0${dd}`;
    return `${yyyy}-${mm}-${dd}`;
});
</script>
