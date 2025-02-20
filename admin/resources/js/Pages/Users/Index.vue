<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import DaisyTable from '@/Components/DaisyTable.vue';
import { useForm, router } from '@inertiajs/vue3';
import UserManagement from '@/Components/UserManagement.vue';
</script>

<template>
    <AuthenticatedLayout title="User Management">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    User Management try
                </h2>
            </div>
        </template>

        <!-- Passing the users prop to the UserManagement component -->
        <UserManagement :users="users" />
    </AuthenticatedLayout>
</template>

<script>
export default {
    props: { users: Object }, // Make sure this 'users' is passed from the parent correctly
};
</script>
