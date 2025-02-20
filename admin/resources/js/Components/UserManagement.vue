<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <!-- Add User Button -->
        <button @click="openModal()" class="btn btn-primary">Add User</button>

        <!-- User Table -->
        <DaisyTable :data="users.data">
            <template #actions="{ row }">
                <button @click="openModal(row)" class="btn btn-sm btn-secondary">Edit</button>
            </template>
        </DaisyTable>

        <!-- Pagination -->
        <div class="flex justify-end mt-4 space-x-2">
            <button :disabled="!users.prev_page_url" @click="changePage(users.current_page - 1)"
                class="btn btn-sm">Prev</button>
            <span class="px-4 py-2">{{ users.current_page }} / {{ users.last_page }}</span>
            <button :disabled="!users.next_page_url" @click="changePage(users.current_page + 1)"
                class="btn btn-sm">Next</button>
        </div>

        <!-- User Modal -->
        <dialog ref="modal" class="modal">
            <div class="modal-box">
                <h2 class="text-lg font-bold">{{ isEditing ? 'Edit User' : 'Add User' }}</h2>
                <form @submit.prevent="saveUser">
                    <label class="label">Name</label>
                    <input v-model="userForm.name" type="text" class="input input-bordered w-full mb-2">

                    <label class="label">Email</label>
                    <input v-model="userForm.email" type="email" class="input input-bordered w-full mb-2">

                    <label class="label">Password</label>
                    <input v-if="!isEditing" v-model="userForm.password" type="password"
                        class="input input-bordered w-full mb-2">

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
    props: {
        users: Object,
    },
    data() {
        return {
            showModal: false,
            isEditing: false,
            userForm: useForm({ id: null, name: "", email: "", password: "" }),
        };
    },
    methods: {
        openModal(user = null) {
            this.isEditing = !!user;
            this.userForm = useForm(user ? { ...user, password: "" } : { id: null, name: "", email: "", password: "" });
            this.$refs.modal.showModal();
        },
        closeModal() {
            this.$refs.modal.close();
        },
        saveUser() {
            if (this.isEditing) {
                this.userForm.put(`/users/${this.userForm.id}`, { onSuccess: () => this.closeModal() });
            } else {
                this.userForm.post('/users', { onSuccess: () => this.closeModal() });
            }
        },
        deleteUser(id) {
            if (confirm("Are you sure?")) {
                router.delete(`/users/${id}`);
            }
        },
        changePage(page) {
            router.get(`/users?page=${page}`);
        },
    },
};
</script>
