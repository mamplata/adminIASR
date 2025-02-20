<template>
    <div :class="{'dark': isDarkMode}" class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        
        <!-- Search Input -->
        <div class="flex mb-2">
            <input v-model="searchQuery" type="text" placeholder="Search by name or email" class="input input-bordered w-full dark:bg-gray-700 dark:text-white">
            <button @click="fetchUsers(1)" class="btn btn-primary ml-2">Search</button>
        </div>

        <!-- Add User Button -->
        <button @click="openModal" class="btn btn-primary mb-2">Add User</button>

        <!-- User Table -->
        <DaisyTable :data="users.data" :currentPage="currentPage" :lastPage="users.last_page" @change-page="changePage">
            <!-- No actions slot as editing is disabled -->
        </DaisyTable>

        <!-- User Modal -->
        <dialog ref="modal" class="modal">
            <div class="modal-box dark:bg-gray-800 dark:text-white">
                <h2 class="text-lg font-bold">Add User</h2>
                <form @submit.prevent="saveUser">
                    <label class="label">Name</label>
                    <input v-model="userForm.name" type="text" class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white" required>

                    <label class="label">Email</label>
                    <input v-model="userForm.email" type="email" class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white" required>

                    <label class="label">Password</label>
                    <input v-model="userForm.password" type="password" class="input input-bordered w-full mb-2 dark:bg-gray-700 dark:text-white" required>

                    <div class="modal-action">
                        <button type="button" @click="closeModal" class="btn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';
import { useForm, router } from '@inertiajs/vue3';

export default {
    props: { users: Object },
    data() {
        return {
            searchQuery: "",
            showModal: false,
            currentPage: 1,
            userForm: useForm({ id: null, name: "", email: "", password: "" }),
        };
    },
    methods: {
        openModal() {
            // Reset the form to clear previous data when adding a new user
            this.userForm = useForm({ id: null, name: "", email: "", password: "" });
            this.$refs.modal.showModal();
        },
        closeModal() {
            this.$refs.modal.close();
        },
        saveUser() {
            // Posting the user data to create a new user
            this.userForm.post('/users', {
                onSuccess: () => this.closeModal(),
            });
        },
        changePage(page) {
            this.currentPage = page;
            this.fetchUsers(page);
        },
        fetchUsers(page) {
            router.get(`/users`, { page: page, search: this.searchQuery });
        },
    },
    components: {
        DaisyTable,
    }
};
</script>
