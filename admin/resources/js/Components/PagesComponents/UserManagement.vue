<template>
    <div :class="{ 'dark': isDarkMode }" class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- Search Input -->
        <div class="flex mb-2">
            <input v-model="searchQuery" type="text" placeholder="Search by name or email"
                class="input input-bordered w-full dark:bg-gray-700 dark:text-white">
            <button @click="fetchUsers(1)" :disabled="loading"
                class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2">
                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Search</span>
            </button>
        </div>

        <!-- Add User Button -->
        <button @click="openModal" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">Add
            User</button>

        <!-- Success Notification -->
        <transition name="fade">
            <div v-if="successMessage" class="alert alert-success shadow-lg mb-4">
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <!-- User Table -->
        <div v-if="users.data.length > 0">
            <DaisyTable :data="users.data" :currentPage="users.current_page" :lastPage="users.last_page"
                @change-page="fetchLogs" />
        </div>
        <div v-else class="text-center py-4 text-gray-500 dark:text-gray-300">
            No users found.
        </div>

        <DaisyModal ref="modal" title="Add User">
            <template #default>
                <form @submit.prevent="saveUser">
                    <label class="label">Name</label>
                    <input v-model="userForm.name" type="text"
                        class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white" required />

                    <label class="label">Email</label>
                    <input v-model="userForm.email" type="email"
                        class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white" required />

                    <label class="label">Password</label>
                    <input v-model="userForm.password" type="password"
                        class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white" required />

                    <!-- Submit Button with Loading State -->
                    <div class="modal-action">
                        <button type="button" @click="closeModal" class="btn">Cancel</button>
                        <button type="submit" class="btn btn-success text-white hover:bg-[#20714c]">
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
    props: { users: Object },
    data() {
        return {
            searchQuery: "",
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
                    setTimeout(() => this.successMessage = "", 4000);
                    this.userForm.reset();
                    this.fetchUsers(1);  // Refresh users after adding a user
                },
                onError: () => {
                    this.successMessage = "Error adding user. Try again!";
                }
            });
        },
        changePage(page) {
            this.currentPage = page;
            this.fetchUsers(page);  // Fetch data for the new page
        },
        fetchUsers(page) {
            this.loading = true;
            router.get(`/users`, { page: page, search: this.searchQuery }, {
                onFinish: () => {
                    this.loading = false;
                }
            });
        },
    },
    components: {
        DaisyTable,
        DaisyModal,
    }
};
</script>
