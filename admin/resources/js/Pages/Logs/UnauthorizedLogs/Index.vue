<script setup>
import UnauthorizedLogs from '@/Components/PagesComponents/UnauthorizedLogs/UnauthorizedLogs.vue';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
</script>

<template>
    <div>
        <AuthenticatedLayout title="Unauthorized Logs">
            <template #header>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">
                        Unauthorized Logs
                    </h2>
                </div>
            </template>

            <UnauthorizedLogs :unauthorizedLogs="unauthorizedLogs" :filters="filters" />
        </AuthenticatedLayout>
    </div>
</template>

<script>
export default {
    props: {
        unauthorizedLogs: Object,
        filters: Object,
    }
};
</script>
