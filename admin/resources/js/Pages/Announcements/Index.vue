<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Announcements from '@/Components/PagesComponents/Announcements/Announcements.vue';
</script>

<template>
    <div>
        <AuthenticatedLayout title=" Announcement Management">
            <template #header>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">
                        Announcement Management
                    </h2>
                </div>
            </template>


            <Announcements :announcements="announcements" :searchDepartments="searchDepartments" />
        </AuthenticatedLayout>
    </div>
</template>

<script>
export default {
    props: { announcements: Object, searchDepartments: Array },
};
</script>
