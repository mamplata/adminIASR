<template>
    <div :class="{'dark': isDarkMode}" class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Audit Logs</h2>

        <!-- Filters (Dropdown Selection) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-2 mb-4">
            <!-- Action Selection -->
            <select v-model="selectedAction" class="input input-bordered w-full dark:bg-gray-700 dark:text-white">
                <option value="" disabled>Select Action</option>
                <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
            </select>

            <!-- Model Selection -->
            <select v-model="selectedModel" class="input input-bordered w-full dark:bg-gray-700 dark:text-white">
                <option value="" disabled>Select Model</option>
                <option v-for="model in models" :key="model" :value="model">{{ model }}</option>
            </select>

            <!-- Admin Selection -->
            <select v-model="selectedAdmin" class="input input-bordered w-full dark:bg-gray-700 dark:text-white">
                <option value="" disabled>Select Admin</option>
                <option v-for="admin in admins" :key="admin.id" :value="admin.id">{{ admin.name }}</option>
            </select>
        </div>

        <button @click="fetchLogs(1)" class="btn btn-success text-white hover:bg-[#20714c] mb-2">Search</button>

        <!-- Audit Log Table -->
        <DaisyTable :data="auditLogs.data" :currentPage="auditLogs.current_page" :lastPage="auditLogs.last_page"
            @change-page="fetchLogs" />
    </div>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';
import { router } from '@inertiajs/vue3';

export default {
    props: { 
        auditLogs: Object, 
        admins: Array, 
        actions: Array, 
        models: Array 
    },
    data() {
        return {
            currentPage: 1,
            selectedAction: "",  // Selected action
            selectedModel: "",   // Selected model
            selectedAdmin: ""    // Selected admin
        };
    },
    methods: {
        fetchLogs(page) {
            router.get(route('logs.audit-logs.index'), { 
                action: this.selectedAction, 
                model: this.selectedModel, 
                admin_id: this.selectedAdmin,
                page: page
            });
        },
        changePage(page) {
            this.currentPage = page;
            this.fetchLogs(page);
        }
    },
    components: { DaisyTable }
};
</script>
