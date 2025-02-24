<template>
    <div class="p-6 overflow-hidden rounded-md shadow-md">
        <!-- Mobile Layout: visible on screens smaller than sm -->
        <div class="sm:hidden mb-2">
            <!-- Flex container in column direction -->
            <div class="flex flex-col space-y-2">
                <!-- Row 1: Input field -->
                <div>
                    <input v-model="searchQuery" type="text" placeholder="Search by name or email"
                        class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <!-- Row 2: Two columns for Search and Reset -->
                <div class="flex space-x-2">
                    <button @click="fetchUsers(1)" :disabled="loading"
                        class="flex-1 btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        <span v-else>Search</span>
                    </button>
                    <button @click="resetSearch"
                        class="flex-1 btn text-white btn-secondary shadow-lg hover:bg-gray-400">
                        Reset
                    </button>
                </div>

                <!-- Row 3: Add User button -->
                <div>
                    <button @click="openModal" class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                        Add User
                    </button>
                </div>
            </div>
        </div>


        <!-- Desktop Layout: visible on screens sm and larger -->
        <div class="hidden sm:block">
            <!-- Search Input with Search and Reset Buttons -->
            <div class="flex mb-2">
                <input v-model="searchQuery" type="text" placeholder="Search by name or email"
                    class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <button @click="fetchUsers(1)" :disabled="loading"
                    class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Search</span>
                </button>
                <button @click="resetSearch" class="btn text-white btn-secondary shadow-lg hover:bg-gray-400 ml-2">
                    Reset
                </button>
            </div>

            <!-- Add User Button on separate row -->
            <button @click="openModal" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
                Add User
            </button>
        </div>

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- User Table -->
        <DaisyTable :data="users.data" :currentPage="users.current_page" :lastPage="users.last_page"
            @change-page="fetchUsers" />

        <!-- Add User Modal -->
        <DaisyModal :isDarkMode="isDarkMode" ref="modal" title="Add User">
            <template #default>
                <form @submit.prevent="saveUser">
                    <label class="label text-gray-700 dark:text-gray-300">Name</label>
                    <input v-model="userForm.name" type="text"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <label class="label text-gray-700 dark:text-gray-300">Email</label>
                    <input v-model="userForm.email" type="email"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <label class="label text-gray-700 dark:text-gray-300">Password</label>
                    <input v-model="userForm.password" type="password"
                        class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                        required />

                    <!-- Modal Actions -->
                    <div class="modal-action">
                        <button type="button" @click="closeModal"
                            class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit"
                            class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                            <span v-if="userForm.processing" class="loading loading-spinner loading-sm"></span>
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
import { useForm, router } from '@inertiajs/vue3';
import DaisyModal from '@/Components/DaisyModal.vue';

export default {
    props: { users: Object, search: String },
    data() {
        return {
            searchQuery: this.search || "",
            currentPage: 1,
            loading: false,
            successMessage: "",
            userForm: useForm({ id: null, name: "", email: "", password: "" }),
        };
    },
    methods: {
        closeModal() {
            this.$refs.modal.closeModal();
        },
        openModal() {
            this.$refs.modal.showModal();
        },
        saveUser() {
            this.userForm.post('/users', {
                onSuccess: () => {
                    this.$refs.modal.closeModal();
                    this.successMessage = "User added successfully!";
                    setTimeout(() => (this.successMessage = ""), 4000);
                    this.userForm.reset();
                    this.fetchUsers(1);
                },
                onError: () => {
                    this.successMessage = "Error adding user. Try again!";
                }
            });
        },
        changePage(page) {
            this.currentPage = page;
            this.fetchUsers(page);
        },
        fetchUsers(page) {
            this.loading = true;
            router.get(`/users`, { page: page, search: this.searchQuery }, {
                preserveState: true,
                onFinish: () => {
                    this.loading = false;
                }
            });
        },
        resetSearch() {
            this.searchQuery = "";
            this.fetchUsers(1);
        },
    },
    components: {
        DaisyTable,
        DaisyModal,
    },
};
</script>
