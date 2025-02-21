<template>
    <div :class="{'dark': isDarkMode}" class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Audit Logs</h2>

        <!-- Filters -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 mb-4">
            <input v-model="filters.action" type="text" placeholder="Action" class="input input-bordered w-full dark:bg-gray-700 dark:text-white" />
            <input v-model="filters.model" type="text" placeholder="Model" class="input input-bordered w-full dark:bg-gray-700 dark:text-white" />
            <select v-model="filters.admin_id" class="input input-bordered w-full dark:bg-gray-700 dark:text-white">
                <option value="">Select Admin</option>
                <option v-for="admin in admins" :key="admin.id" :value="admin.id">{{ admin.name }}</option>
            </select>
            <input v-model="filters.start_date" type="date" class="input input-bordered w-full dark:bg-gray-700 dark:text-white" />
            <input v-model="filters.end_date" type="date" class="input input-bordered w-full dark:bg-gray-700 dark:text-white" />
        </div>
        <button @click="fetchLogs(1)" class="btn btn-success text-white hover:bg-[#20714c] mb-2">Search</button>

        <!-- Audit Log Table -->
        <DaisyTable :data="auditLogs.data" :currentPage="currentPage" :lastPage="auditLogs.last_page" @change-page="changePage">
        </DaisyTable>
    </div>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';
import { router } from '@inertiajs/vue3';

export default {
    props: { auditLogs: Object, filters: Object, admins: Array },
    data() {
            return {
                currentPage: 1,
            filters: { ...this.filters },
        };
    },
    methods: {
        fetchLogs(page) {
            router.get(route('logs.audit-logs.index'), { ...this.filters, page: page });
        },
        changePage(page) {
            this.currentPage = page;
            this.fetchLogs(page);
        }
    },
    components: { DaisyTable }
};
</script>
