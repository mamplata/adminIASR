<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- Search Input -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-2 mb-4">
            <input v-model="searchQuery" type="text" placeholder="Search publisher"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500 col-span-2" />

            <select v-model="selectedDepartment"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500 mb-2">
                <option value="" disabled>Select Department</option>
                <option v-for="department in searchDepartments" :key="department" :value="department">
                    {{ department }}
                </option>
            </select>

            <button @click="fetchAnnouncements(1)" :disabled="loading"
                class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2 dark:bg-green-700 dark:hover:bg-green-600">
                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Search</span>
            </button>
        </div>

        <!-- Add Announcement Button -->
        <button @click="openModal" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
            Add Announcement
        </button>

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- Table of Announcements -->
        <DaisyTable :data="announcements.data" :currentPage="announcements.current_page"
            :lastPage="announcements.last_page" @change-page="fetchAnnouncements" />

        <!-- Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
            <div v-for="announcement in announcements.data" :key="announcement.id"
                class="card bg-white rounded-md shadow-md dark:bg-dark-eval-1 p-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ announcement.publisher }}
                </h3>
                <p class="text-gray-700 dark:text-gray-300">Department: {{ announcement.department }}</p>
                <p class="text-gray-700 dark:text-gray-300">
                    Publication Date: {{ announcement.publication_date }}
                </p>
                <div v-if="announcement.type === 'text'" class="mt-2">
                    <h4 class="font-semibold text-gray-800 dark:text-white">
                        {{ announcement.content.title }}
                    </h4>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ announcement.content.body }}
                    </p>
                </div>
                <div v-else-if="announcement.type === 'image'" class="mt-2">
                    <img :src="announcement.content.file_path" :alt="announcement.content.file_name"
                        class="rounded-md w-full object-cover" />
                </div>
            </div>
        </div>


        <!-- Modal for Creating an Announcement -->
        <DaisyModal ref="modal" title="Add Announcement">
            <template #default>
                <form @submit.prevent="saveAnnouncement">
                    <!-- Publisher -->
                    <label class="label text-gray-900 dark:text-white">Publisher</label>
                    <input v-model="announcementForm.publisher" type="text"
                        class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <!-- Department Dropdown -->
                    <label class="label text-gray-900 dark:text-white">Department</label>
                    <select v-model="announcementForm.department"
                        class="input w-full input-bordered mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
                        <option value="" disabled>Select Department</option>
                        <option v-for="dept in departments" :key="dept" :value="dept">
                            {{ dept }}
                        </option>
                    </select>

                    <!-- Publication Date -->
                    <label class="label text-gray-900 dark:text-white">Publication Date</label>
                    <input v-model="announcementForm.publication_date" type="date"
                        class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <!-- Type (text/image) -->
                    <label class="label text-gray-900 dark:text-white">Type</label>
                    <select v-model="announcementForm.type"
                        class="select select-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required>
                        <option disabled value="">-- Select Announcement Type --</option>
                        <option value="text">Text</option>
                        <option value="image">Image</option>
                    </select>

                    <!-- Conditionally Render Content Fields -->
                    <!-- For text-based announcements -->
                    <div v-if="announcementForm.type === 'text'">
                        <label class="label text-gray-900 dark:text-white">Title</label>
                        <input v-model="extraContent.title" type="text"
                            class="input input-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                            required />

                        <label class="label text-gray-900 dark:text-white">Body</label>
                        <textarea v-model="extraContent.body"
                            class="textarea textarea-bordered w-full mb-2 bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                            required></textarea>
                    </div>

                    <!-- For image-based announcements -->
                    <div v-else-if="announcementForm.type === 'image'">
                        <label class="label text-gray-900 dark:text-white">Upload Image</label>
                        <div class="relative flex flex-col items-center mb-2">
                            <input type="file" accept="image/*" @change="handleImageUpload" class="hidden"
                                id="fileInput" required />
                            <label for="fileInput"
                                class="cursor-pointer px-4 py-2 w-full border border-gray-300 rounded-md text-center bg-white text-gray-900 dark:bg-gray-800 dark:text-white dark:border-gray-600">
                                {{ fileName || 'Choose File' }}
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button with Loading State -->
                    <div class="modal-action">
                        <button type="button" @click="closeModal"
                            class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit"
                            class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                            <span v-if="announcementForm.processing" class="loading loading-spinner loading-sm"></span>
                            <span v-else>Save</span>
                        </button>
                    </div>
                </form>
            </template>
        </DaisyModal>
    </div>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';
import DaisyModal from '@/Components/DaisyModal.vue';
import { useForm, router } from '@inertiajs/vue3';

export default {
    props: {
        announcements: Object,
        searchDepartments: Array,
    },
    data() {
        return {
            searchQuery: "",
            loading: false,
            selectedDepartment: "",
            successMessage: "",
            announcementForm: useForm({
                publisher: "",
                department: "",
                publication_date: "", // New field for publication date
                type: "",
                content: "",
                file: null, // Holds the file object for image announcements
            }),
            fileName: '',
            extraContent: {
                title: "",
                body: "",
            },
            departments: ['CCS', 'CAS', 'CHAS', 'COE', 'CBAA', 'COED'],
        };
    },
    methods: {
        openModal() {
            this.resetForm();
            this.$refs.modal.showModal();
        },
        closeModal() {
            this.$refs.modal.closeModal();
        },
        resetForm() {
            this.announcementForm.reset();
            this.extraContent = { title: "", body: "" };
            this.fileName = '';
        },
        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.fileName = file.name;
            // Directly store the file for metadata processing on the backend.
            this.announcementForm.file = file;
        },
        saveAnnouncement() {
            if (this.announcementForm.type === 'text') {
                // Set content to a JSON string for text announcements.
                this.announcementForm.content = JSON.stringify({
                    title: this.extraContent.title,
                    body: this.extraContent.body,
                });
            } else if (this.announcementForm.type === 'image') {
                // For image announcements, assign the file to the content field.
                this.announcementForm.content = this.announcementForm.file;
            }

            this.announcementForm.post('/announcements', {
                forceFormData: true, // Ensures file is sent as FormData
                onSuccess: () => {
                    this.closeModal();
                    this.successMessage = "Announcement added successfully!";
                    setTimeout(() => (this.successMessage = ""), 4000);
                    this.announcementForm.reset();
                    this.extraContent = { title: "", body: "" };
                    this.fileName = '';
                },
                onError: () => {
                    this.successMessage = "Error adding announcement. Try again!";
                },
            });
        },

        fetchAnnouncements(page) {
            this.loading = true;
            router.get(
                '/announcements',
                {
                    page,
                    search: this.searchQuery,
                    department: this.selectedDepartment,
                },
                {
                    onFinish: () => {
                        this.loading = false;
                    }
                }
            );
        },
    },
    components: {
        DaisyTable,
        DaisyModal,
    },
};
</script>
