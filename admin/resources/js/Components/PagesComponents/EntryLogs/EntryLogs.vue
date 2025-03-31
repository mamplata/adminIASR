<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Entry Logs</h2>

        <!-- Entry Log Filter Panel -->
        <EntryLogFilterPanel v-model:search="search" v-model:timeType="timeType" v-model:status="status"
            v-model:dateFrom="dateFrom" v-model:dateTo="dateTo" @search="fetchLogs(1)" @reset="resetSearch"
            :loading="loading" />

        <!-- DaisyTable Component for displaying logs -->
        <DaisyTable :data="entryLogs.data" :currentPage="entryLogs.current_page" :lastPage="entryLogs.last_page"
            @change-page="fetchLogs" />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import EntryLogFilterPanel from './EntryLogFilterPanel.vue';
import DaisyTable from '@/Components/Daisy/DaisyTable.vue';

const { props: page } = usePage();

const props = defineProps({
    entryLogs: Object,
});

console.log(page);

// Initialize filter states using page props.
const search = ref(page.search || '');
const timeType = ref(page.filters.time_type || '');
const status = ref(page.filters.status || '');
const dateFrom = ref(page.filters.date_from || '');
const dateTo = ref(page.filters.date_to || '');
const loading = ref(false);

// Function to fetch logs using the current filter values and the given page.
function fetchLogs(pageNumber) {
    loading.value = true;

    const filters = {
        search: search.value,
        time_type: timeType.value,
        status: status.value,
        date_from: dateFrom.value || null,
        date_to: dateTo.value || null,
        page: pageNumber,
    };

    router.get(route('logs.entry-logs.index'), filters, {
        preserveState: true,
        onFinish: () => {
            loading.value = false;
        },
    });
}

// Reset filter values and then fetch logs starting from page 1.
function resetSearch() {
    search.value = '';
    timeType.value = '';
    status.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    fetchLogs(1);
}
</script>
