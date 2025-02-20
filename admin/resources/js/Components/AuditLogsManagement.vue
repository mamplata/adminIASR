<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- Filter Section -->
        <form @submit.prevent="applyFilters" class="flex flex-col md:flex-row gap-4">
            <div class="form-group flex-1">
                <label for="action">Action</label>
                <select v-model="filters.action" id="action" class="input input-bordered w-full mb-2">
                    <option value="">All</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                </select>
            </div>

            <div class="form-group flex-1">
                <label for="model">Model</label>
                <select v-model="filters.model" id="model" class="input input-bordered w-full mb-2">
                    <option value="">All</option>
                    <option value="User">User</option>
                    <option value="Post">Post</option>
                    <!-- Add other models as needed -->
                </select>
            </div>

            <div class="form-group flex-1">
                <label for="admin_id">Admin</label>
                <input v-model="filters.admin_id" type="text" id="admin_id" class="input input-bordered w-full mb-2"
                    placeholder="Admin ID">
            </div>

            <div class="form-group flex-1">
                <label for="start_date">Start Date</label>
                <input v-model="filters.start_date" type="date" id="start_date"
                    class="input input-bordered w-full mb-2">
            </div>

            <div class="form-group flex-1">
                <label for="end_date">End Date</label>
                <input v-model="filters.end_date" type="date" id="end_date" class="input input-bordered w-full mb-2">
            </div>

            <div class="flex items-end gap-4 mt-4">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>

        <!-- Audit Logs Table -->
        <div class="mt-4">
            <DaisyTable :data="auditLogs.data" :currentPage="currentPage" :lastPage="auditLogs.last_page"
                @change-page="changePage" />
        </div>

        <!-- Pagination -->
        <div class="flex justify-end mt-4 space-x-2">
            <button :disabled="!auditLogs.prev_page_url" @click="changePage(currentPage - 1)"
                class="btn btn-sm">Prev</button>
            <span class="px-4 py-2">{{ currentPage }} / {{ auditLogs.last_page }}</span>
            <button :disabled="!auditLogs.next_page_url" @click="changePage(currentPage + 1)"
                class="btn btn-sm">Next</button>
        </div>
    </div>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';
import { useForm, router } from '@inertiajs/vue3';

export default {
    props: {
        auditLogs: Object,  // Audit logs data passed from the parent component
    },
    data() {
        return {
            filters: {
                action: '',
                model: '',
                admin_id: '',
                start_date: '',
                end_date: '',
            },
            currentPage: 1,  // To keep track of current page
        };
    },
    methods: {
        // Method to change page
        changePage(page) {
            this.currentPage = page;
            this.fetchLogs(page);
        },

        // Fetch logs with filters and pagination
        fetchLogs(page) {
            this.$inertia.get('/audit-logs', { ...this.filters, page });
        },

        // Apply filters
        applyFilters() {
            this.fetchLogs(1);  // Always start from the first page when applying new filters
        }
    },
    components: {
        DaisyTable,
    }
};
</script>

<style scoped>
/* Add custom styles here if needed */
</style>
