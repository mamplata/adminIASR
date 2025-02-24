<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- Search Input -->
        <div class="flex mb-2">
            <input v-model="searchQuery" type="text" placeholder="Search publisher"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500" />

            <select v-model="selectedDepartment"
                class="input input-bordered w-full bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500 mb-2 ml-2">
                <option value="" disabled>Select Department</option>
                <option v-for="department in searchDepartments" :key="department" :value="department">
                    {{ department }}
                </option>
            </select>

            <button @click="fetchAnnouncements(1)" :disabled="loading"
                class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2">
                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Search</span>
            </button>
            <button @click="resetSearch" class="btn text-white btn-secondary shadow-lg hover:bg-gray-400 ml-2">
                Reset
            </button>
        </div>

        <!-- Add Announcement Button -->
        <button @click="openModal('Add')" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
            Add Announcement
        </button>

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>
        <DaisyTable :data="announcements.data" :excludedColumns="['id', 'content']"
            :currentPage="announcements.current_page" :lastPage="announcements.last_page"
            @change-page="fetchAnnouncements">
            <template #actions="{ row }">
                <div class="flex gap-2">
                    <button @click="viewAnnouncement(row)"
                        class="btn text-white btn-primary shadow-lg hover:bg-blue-700 mb-2">
                        View
                    </button>
                    <button @click="openModal('Edit', row)"
                        class="btn text-white btn-warning shadow-lg hover:bg-yellow-600 mb-2">
                        Edit
                    </button>
                    <button @click="confirmDeleteAnnouncement(row.id)"
                        class="btn text-white btn-error shadow-lg hover:bg-red-700 mb-2">
                        Delete
                    </button>
                </div>
            </template>
        </DaisyTable>

        <DaisyConfirm :visible="showConfirm" title="Delete Announcement"
            message="Are you sure you want to delete this announcement?" @confirm="handleConfirmDelete"
            @cancel="handleCancelDelete" />

        <!-- Conditionally render the announcement card -->
        <DaisyCardAnnouncement v-if="selectedAnnouncement" :announcement="selectedAnnouncement"
            @close="selectedAnnouncement = null" />

        <!-- Modal for Creating an Announcement -->
        <DaisyModal ref="modal" :title="currentAction">
            <template #default>
                <form @submit.prevent="isEditing ? updateAnnouncement() : saveAnnouncement()">
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
                        <label class="label text-gray-900 dark:text-white">{{ uploadImage }}</label>
                        <div class="relative flex flex-col items-center mb-2">
                            <input type="file" accept="image/*" @change="handleImageUpload" class="hidden"
                                id="fileInput" :required="isFileRequired" />
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
import DaisyCardAnnouncement from '@/Components/DaisyCardAnnouncement.vue';
import DaisyConfirm from '../DaisyConfirm.vue';

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
            selectedAnnouncement: null,
            currentAction: "Add",
            currentRow: null,
            isEditing: false,
            uploadImage: "Upload Image",
            hasImage: false, 
            showConfirm: false,
            announcementToDelete: null,
        };
    },
    computed: {
        // Required if type is image and either not editing or editing but no file exists.
        isFileRequired() {
        // When not editing, or when editing but there's no file selected (or already set)
        return this.announcementForm.type === 'image' && (!this.isEditing || !this.hasImage);
        },
    },
    methods: {
        openModal(action, row = null) {
            this.resetForm();
            // Set mode and populate form if editing
            if (action === 'Edit' && row) {
                this.isEditing = true;
                this.announcementForm.publisher = row.publisher;
                this.announcementForm.department = row.department;

                let dateObj = new Date(row.publication_date);
                let formattedDate = dateObj.toISOString().split('T')[0];  
                this.announcementForm.publication_date = formattedDate;
                this.announcementForm.type = row.type;
                if (row.type === 'text') {
                    try {
                        this.extraContent.title = row.content.title;
                        this.extraContent.body = row.content.body;
                    } catch (e) {
                        console.error('Failed parsing text announcement content', e);
                    }
                } else {
                    this.uploadImage = "Upload New Image (Optional)";
                    this.hasImage = true;
                    this.announcementForm.content = row.content;
                }
                // For image type, you may optionally set fileName if stored.
                this.currentRow = row;
            } else {
                this.currentRow = null;
            }
            this.currentAction = action + " Announcement";
            // Optionally, pass the mode to the modal if needed.
            this.$refs.modal.mode = action;
            this.$refs.modal.showModal();
        },
        closeModal() {
            this.isEditing = false;
            this.uploadImage = "Upload New Image";
            this.announcementForm.content = '';
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
        updateAnnouncement() {
            if (this.announcementForm.type === 'text') {
                this.announcementForm.content = JSON.stringify({
                    title: this.extraContent.title,
                    body: this.extraContent.body,
                });
            } else if (this.announcementForm.type === 'image') {
                if (this.announcementForm.file != null) {
                    this.announcementForm.content = this.announcementForm.file;
                }
            }

            // Use POST with _method override instead of PUT
            this.announcementForm.post(`/announcements/${this.currentRow.id}`, {
                _method: 'PUT',
                forceFormData: true,
                onSuccess: () => {
                    this.closeModal();
                    this.successMessage = "Announcement updated successfully!";
                    setTimeout(() => (this.successMessage = ""), 4000);
                    this.announcementForm.reset();
                    this.extraContent = { title: "", body: "" };
                    this.fileName = '';
                },
                onError: () => {
                    this.successMessage = "Error updating announcement. Try again!";
                },
            });
        },
        confirmDeleteAnnouncement(announcementId) {
            // Store the announcement ID and show the confirmation modal
            this.announcementToDelete = announcementId;
            this.showConfirm = true;
        },
        handleConfirmDelete() {
            // User confirmed: perform deletion logic
            this.announcementForm.delete(`/announcements/${this.announcementToDelete}`, {
                onSuccess: () => {
                this.successMessage = "Announcement deleted successfully!";
                setTimeout(() => { this.successMessage = ""; }, 4000);
                },
                onError: () => {
                this.successMessage = "Error deleting announcement. Please try again!";
                setTimeout(() => { this.successMessage = ""; }, 4000);
                },
            });
            // Reset the state
            this.announcementToDelete = null;
            this.showConfirm = false;
        },
        handleCancelDelete() {
            // User cancelled: just hide the confirmation modal
            this.announcementToDelete = null;
            this.showConfirm = false;
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
                    preserveState: true,
                    onFinish: () => {
                        this.loading = false;
                    }
                }
            );
        },
        resetSearch() {
            this.searchQuery = "";
            this.selectedDepartment = "";
            this.fetchAnnouncements(1);
        },
        viewAnnouncement(row) {
            this.selectedAnnouncement = row;
        },
    },
    components: {
        DaisyTable,
        DaisyModal,
        DaisyCardAnnouncement,
        DaisyConfirm
    },
};
</script>
