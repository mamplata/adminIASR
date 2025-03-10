<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Semesters from '@/Components/PagesComponents/Semesters/Semesters.vue';
</script>

<template>
    <div>
        <AuthenticatedLayout title="Settings">
            <template #header>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">
                        Settings
                    </h2>
                </div>
            </template>

            <!-- Passing the users prop to the UserManagement component -->
            <Semesters :currentYear="currentYear" :semester="semester" />
        </AuthenticatedLayout>
    </div>
</template>

<script>
export default {
    props: { currentYear: Number, semester: Object },
};
</script>
