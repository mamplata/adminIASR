<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- First Row: Search by Publisher & Department -->
        <div class="flex flex-col md:flex-row gap-2 mb-4">
            <!-- Search by Publisher -->
            <input v-model="searchQuery" type="text" placeholder="Search publisher"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- Department Dropdown -->
            <select v-model="selectedDepartment"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
                <option value="" disabled>Select Department</option>
                <option v-for="department in searchDepartments" :key="department" :value="department">
                    {{ department }}
                </option>
            </select>
        </div>

        <!-- Second Row: Date Range and Buttons -->
        <div class="flex flex-col md:flex-row items-center gap-2 mb-4">
            <label class="whitespace-nowrap">Range Date:</label>
            <!-- Start Date with min and max -->
            <input v-model="startDate" type="date" :min="minStartDate" :max="maxStartDate"
                class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- End Date with min and max -->
            <input v-model="endDate" type="date" :min="minEndDate" :max="maxEndDate"
                class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <!-- Flex Container for Search & Reset Buttons -->
            <div class="flex space-x-2 w-full md:w-auto">
                <button @click="fetchAnnouncements(1)" :disabled="loading"
                    class="flex-1 btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Search</span>
                </button>
                <button @click="resetSearch" class="flex-1 btn text-white btn-secondary shadow-lg hover:bg-[#7b7b7b]">
                    Reset
                </button>
            </div>
        </div>

        <!-- Add Announcement Button -->
        <button @click="openModal('Add')"
            class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mt-2 w-full md:w-auto">
            Add Announcement
        </button>

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4 mt-2">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Announcements Table -->
        <DaisyTable :data="announcements.data" :excludedColumns="['id', 'content']"
            :currentPage="announcements.current_page" :lastPage="announcements.last_page"
            @change-page="fetchAnnouncements">
            <template #actions="{ row }">
                <div class="flex gap-2 flex-wrap">
                    <button @click="viewAnnouncement(row)"
                        class="btn text-white btn-primary shadow-lg hover:bg-blue-700">
                        View
                    </button>
                    <button @click="openModal('Edit', row)"
                        class="btn text-white btn-warning shadow-lg hover:bg-yellow-600">
                        Edit
                    </button>
                    <button @click="confirmDeleteAnnouncement(row.id)"
                        class="btn text-white btn-error shadow-lg hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </template>
        </DaisyTable>

        <!-- Confirm Delete -->
        <DaisyConfirm :visible="showConfirm" title="Delete Announcement"
            message="Are you sure you want to delete this announcement?" @confirm="handleConfirmDelete"
            @cancel="handleCancelDelete" />

        <!-- Announcement Card -->
        <DaisyCardAnnouncement v-if="selectedAnnouncement" :announcement="selectedAnnouncement"
            @close="selectedAnnouncement = null" />

        <!-- Modal for Creating/Editing an Announcement -->
        <DaisyModal ref="modal" :title="currentAction">
            <!-- ...Your existing modal content from the original code... -->
        </DaisyModal>
    </div>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';
import DaisyModal from '@/Components/DaisyModal.vue';
import DaisyCardAnnouncement from '@/Components/DaisyCardAnnouncement.vue';
import DaisyConfirm from '../DaisyConfirm.vue';
import { useForm, router } from '@inertiajs/vue3';

export default {
    props: {
        announcements: Object,
        searchDepartments: Array,
    },
    data() {
        return {
            // Existing fields
            searchQuery: "",
            loading: false,
            selectedDepartment: "",
            successMessage: "",
            announcementForm: useForm({
                publisher: "",
                department: "",
                publication_date: "",
                type: "",
                content: "",
                file: null,
            }),
            fileName: "",
            extraContent: { title: "", body: "" },
            departments: ['CCS', 'CAS', 'CHAS', 'COE', 'CBAA', 'COED'],
            selectedAnnouncement: null,
            currentAction: "Add",
            currentRow: null,
            isEditing: false,
            uploadImage: "Upload Image",
            hasImage: false,
            showConfirm: false,
            announcementToDelete: null,

            // New fields for date range
            startDate: "",
            endDate: "",
        };
    },
    computed: {
        // Define a fixed minimum date (adjust as needed)
        minStartDate() {
            return "1900-01-01";
        },
        // For start date, max is either the selected end date or today's date
        maxStartDate() {
            return this.endDate || new Date().toISOString().split('T')[0];
        },
        // For end date, min is either the selected start date or the fixed min
        minEndDate() {
            return this.startDate || "1900-01-01";
        },
        // End date max is today's date
        maxEndDate() {
            return new Date().toISOString().split('T')[0];
        },
        isFileRequired() {
            return (
                this.announcementForm.type === 'image' &&
                (!this.isEditing || !this.hasImage)
            );
        },
    },
    methods: {
        /**
         * Fetch announcements from the server with filters:
         * - search by publisher
         * - department
         * - date range (startDate, endDate)
         */
        fetchAnnouncements(page) {
            this.loading = true;
            router.get(
                '/announcements',
                {
                    page,
                    search: this.searchQuery,
                    department: this.selectedDepartment,
                    start_date: this.startDate,
                    end_date: this.endDate,
                },
                {
                    preserveState: true,
                    onFinish: () => {
                        this.loading = false;
                    },
                }
            );
        },

        /**
         * Reset all search filters, including the date range,
         * then fetch the first page of announcements.
         */
        resetSearch() {
            this.searchQuery = "";
            this.selectedDepartment = "";
            this.startDate = "";
            this.endDate = "";
            this.fetchAnnouncements(1);
        },

        // ... Other methods such as openModal, closeModal, viewAnnouncement, etc.
    },
    components: {
        DaisyTable,
        DaisyModal,
        DaisyCardAnnouncement,
        DaisyConfirm,
    },
};
</script>
