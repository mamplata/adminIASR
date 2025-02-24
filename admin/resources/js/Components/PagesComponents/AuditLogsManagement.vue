<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Audit Logs</h2>

        <!-- Filters (Dropdown Selection) -->
        <div class="w-full mb-4">
            <!-- First Row: Dropdown Selections -->
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Action Selection -->
                <select v-model="selectedAction"
                    class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
                    <option value="" disabled>Select Action</option>
                    <option v-for="action in actions" :key="action" :value="action">{{ action }}</option>
                </select>

                <!-- Model Selection -->
                <select v-model="selectedModel"
                    class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
                    <option value="" disabled>Select Model</option>
                    <option v-for="model in models" :key="model" :value="model">{{ model }}</option>
                </select>

                <!-- Admin Selection -->
                <select v-model="selectedAdmin"
                    class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
                    <option value="" disabled>Select Admin</option>
                    <option v-for="admin in admins" :key="admin.id" :value="admin.id">{{ admin.name }}</option>
                </select>
            </div>

            <!-- Second Row: Date Range and Buttons -->
            <div class="flex flex-col md:flex-row items-center gap-2 mt-4">
                <label class="whitespace-nowrap">Range Date:</label>
                <input v-model="startDate" type="date" :max="maxStartDate"
                    class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

                <input v-model="endDate" type="date" :max="maxEndDate"
                    class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

                <button @click="fetchLogs(1)" class="btn btn-success text-white hover:bg-[#20714c] w-full md:w-auto">
                    Search
                </button>

                <button @click="resetFilters" class="btn btn-secondary text-white hover:bg-[#7b7b7b] w-full md:w-auto">
                    Reset
                </button>
            </div>
        </div>

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
            selectedAction: "",
            selectedModel: "",
            selectedAdmin: "",
            startDate: "",
            endDate: "",
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
            router.get(route('logs.audit-logs.index'), filters, { preserveState: true }).then(response => {
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
