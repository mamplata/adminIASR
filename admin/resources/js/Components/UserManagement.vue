<template>
    <div :class="{'dark': isDarkMode}" class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">

        <!-- Search Input -->
        <div class="flex mb-2">
            <input v-model="searchQuery" type="text" placeholder="Search by name or email"
                class="input input-bordered w-full dark:bg-gray-700 dark:text-white">
            <button @click="fetchUsers(1)"
                class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2">Search</button>
        </div>

        <!-- Add User Button -->
        <button @click="openModal" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">Add
            User</button>

        <!-- User Table -->
        <DaisyTable :data="users.data" :currentPage="currentPage" :lastPage="users.last_page"
            @change-page="changePage" />

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

                    <!-- Move the Submit Button Inside the Form -->
                    <div class="modal-action">
                        <button type="button" @click="closeModal" class="btn">Cancel</button>
                        <button type="submit" class="btn btn-success text-white hover:bg-[#20714c]">Save</button>
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
            userForm: useForm({ id: null, name: "", email: "", password: "" }),
        };
    },
    methods: {
        openModal() {
            this.$refs.modal.showModal();
        },
        saveUser() {
            this.userForm.post('/users', {
                onSuccess: () => this.$refs.modal.closeModal(),
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
        DaisyModal,  
    }
};
</script>
