<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { useForm, router } from '@inertiajs/vue3';
import UserManagement from '@/Components/PagesComponents/UserManagement.vue';
import Users from '@/Components/PagesComponents/Users/Users.vue';
</script>

<template>
  <div>
    <AuthenticatedLayout title="User Management">
      <template #header>
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <h2 class="text-xl font-semibold leading-tight">
            User Management
          </h2>
        </div>
      </template>

      <!-- Passing the users prop to the UserManagement component -->
      <Users :users="users" />
    </AuthenticatedLayout>
  </div>
</template>

<script>
export default {
  props: { users: Object },
};
</script>
