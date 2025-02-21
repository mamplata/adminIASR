<template>
    <div :class="{ 'dark': isDarkMode }" class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
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

            <!-- Date Range Filters -->
            <div>
                <input v-model="startDate" type="date" :max="maxStartDate"
                    class="input input-bordered w-full dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
                <input v-model="endDate" type="date" :max="maxEndDate"
                    class="input input-bordered w-full dark:bg-gray-700 dark:text-white" />
            </div>
        </div>

        <button @click="fetchLogs(1)" class="btn btn-success text-white hover:bg-[#20714c] mb-2">Search</button>
        <button @click="resetFilters" class="btn btn-secondary text-white hover:bg-[#7b7b7b] mb-2">Reset</button>

        <!-- Display "Data not available" if no data is present -->
        <p v-if="noData" class="text-center text-gray-500">Data not available</p>

        <!-- Audit Log Table -->
<<<<<<< HEAD
        <DaisyTable :data="auditLogs.data" :currentPage="auditLogs.current_page" :lastPage="auditLogs.last_page"
            @change-page="fetchLogs" />
=======
        <DaisyTable v-if="!noData" :data="auditLogs.data" :currentPage="auditLogs.current_page"
            :lastPage="auditLogs.last_page" @change-page="fetchLogs" />
>>>>>>> de7677711b3b9f2ccfb288a4f80042334f93f91a
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
            selectedAction: "",
            selectedModel: "",
            selectedAdmin: "",
            startDate: "",
            endDate: "",
            noData: false  // Flag to show "Data not available" message
        };
    },
    computed: {
        // Computed property for maxStartDate based on the endDate or today's date
        maxStartDate() {
            if (this.endDate) {
                return this.endDate; // if endDate is selected, use it as maxStartDate
            }
            const today = new Date();
            const yyyy = today.getFullYear();
            let mm = today.getMonth() + 1; // Months are zero-based
            let dd = today.getDate();

            if (mm < 10) mm = `0${mm}`;
            if (dd < 10) dd = `0${dd}`;

            return `${yyyy}-${mm}-${dd}`;
        },
        // Computed property to get today's date for max date in endDate input
        maxEndDate() {
            const today = new Date();
            const yyyy = today.getFullYear();
            let mm = today.getMonth() + 1; // Months are zero-based
            let dd = today.getDate();

            if (mm < 10) mm = `0${mm}`;
            if (dd < 10) dd = `0${dd}`;

            return `${yyyy}-${mm}-${dd}`;
        },
    },
    methods: {
        fetchLogs(page) {
            // Check if no filters are provided (both startDate and endDate are empty), but only do this if it's not a reset
            if (!this.selectedAction && !this.selectedModel && !this.selectedAdmin && !this.startDate && !this.endDate && !this.resetting) {
                this.noData = true;
                return;
            }

            // Reset noData if the search is initiated
            this.noData = false;

            // If there is no startDate, use only endDate for filtering
            const filters = {
                action: this.selectedAction,
                model: this.selectedModel,
                admin_id: this.selectedAdmin,
                start_date: this.startDate || null,
                end_date: this.endDate || null,
                page: page
            };

            // Perform the search
            router.get(route('logs.audit-logs.index'), filters).then(response => {
                if (response.props.auditLogs.data.length === 0) {
                    this.noData = true;  // No results, so show "Data not available"
                }
            });
        },
        changePage(page) {
            this.currentPage = page;
            this.fetchLogs(page);
        },
        // Method to reset all filters
        resetFilters() {
            this.selectedAction = "";
            this.selectedModel = "";
            this.selectedAdmin = "";
            this.startDate = "";
            this.endDate = "";
            this.noData = false;  // Reset "Data not available" flag
            this.resetting = true; // Set flag to indicate that reset is happening
            this.fetchLogs(1);     // Fetch all logs (no filters applied)
            this.resetting = false; // Reset the flag after fetching
        }
    },
    components: { DaisyTable }
};
</script>
