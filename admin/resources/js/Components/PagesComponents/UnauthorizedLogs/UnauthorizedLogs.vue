<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Entry Logs</h2>

        <!-- Entry Log Filter Panel -->
        <UnauthorizedLogFilterPanel v-model:timeType="timeType" v-model:dateFrom="dateFrom" v-model:dateTo="dateTo"
            @search="fetchLogs(1)" @reset="resetSearch" :loading="loading" />

        <!-- DaisyTable Component for displaying logs -->
        <DaisyTable :data="unauthorizedLogs.data" :currentPage="unauthorizedLogs.current_page"
            :lastPage="unauthorizedLogs.last_page" @change-page="fetchLogs" />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import UnauthorizedLogFilterPanel from './UnauthorizedLogFilterPanel.vue';
import DaisyTable from '@/Components/Daisy/DaisyTable.vue';

const { props: page } = usePage();

const props = defineProps({
    unauthorizedLogs: Object,
});

console.log(page);

// Initialize filter states using page props.
const timeType = ref(page.filters.time_type || '');
const dateFrom = ref(page.filters.date_from || '');
const dateTo = ref(page.filters.date_to || '');
const loading = ref(false);

// Function to fetch logs using the current filter values and the given page.
function fetchLogs(pageNumber) {
    loading.value = true;

    const filters = {
        time_type: timeType.value,
        date_from: dateFrom.value || null,
        date_to: dateTo.value || null,
        page: pageNumber,
    };

    router.get(route('logs.unauthorized-logs.index'), filters, {
        preserveState: true,
        onFinish: () => {
            loading.value = false;
        },
    });
}

// Reset filter values and then fetch logs starting from page 1.
function resetSearch() {
    timeType.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    fetchLogs(1);
}
</script>
