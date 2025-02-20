<template>
    <div class="container">
        <h1 class="mb-4">Audit Logs</h1>

        <!-- Filter Section -->
        <div class="mb-4">
            <form @submit.prevent="applyFilters">
                <div class="flex gap-4">
                    <div class="form-group">
                        <label for="action">Action</label>
                        <select v-model="filters.action" id="action" class="form-control">
                            <option value="">All</option>
                            <option value="create">Create</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="model">Model</label>
                        <select v-model="filters.model" id="model" class="form-control">
                            <option value="">All</option>
                            <option value="User">User</option>
                            <option value="Post">Post</option>
                            <!-- Add other models as needed -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="admin_id">Admin</label>
                        <input v-model="filters.admin_id" type="text" id="admin_id" class="form-control"
                            placeholder="Admin ID">
                    </div>

                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input v-model="filters.start_date" type="date" id="start_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input v-model="filters.end_date" type="date" id="end_date" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Table to display audit logs -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Model</th>
                    <th>Model ID</th>
                    <th>Details</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in auditLogs.data" :key="log.id">
                    <td>{{ log.admin ? log.admin.name : 'N/A' }}</td>
                    <td>{{ capitalizeFirstLetter(log.action) }}</td>
                    <td>{{ log.model }}</td>
                    <td>{{ log.model_id }}</td>
                    <td>{{ log.details }}</td>
                    <td>{{ formatDate(log.created_at) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            <pagination :links="auditLogs.links" @go-to-page="goToPage" />
        </div>
    </div>
</template>

<script>
import Pagination from '@/Components/Pagination'; // Optional, if you have a Pagination component

export default {
    props: {
        auditLogs: Object,
        filters: Object
    },
    data() {
        return {
            filters: { ...this.filters } // Initialize filters from the props
        };
    },
    methods: {
        // Helper function to capitalize the first letter of a string
        capitalizeFirstLetter(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
        // Helper function to format the date
        formatDate(date) {
            return new Date(date).toLocaleString();
        },
        // Go to the requested page
        goToPage(page) {
            this.$inertia.get(`/audit-logs?page=${page}`, this.filters);
        },
        // Apply filters
        applyFilters() {
            this.$inertia.get('/audit-logs', this.filters);
        }
    },
    components: {
        Pagination
    }
};
</script>

<style scoped>
/* Add custom styles here */
</style>
