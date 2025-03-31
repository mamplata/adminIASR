<script setup>
import EntryLogs from '@/Components/PagesComponents/EntryLogs/EntryLogs.vue';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
</script>

<template>
    <div>
        <AuthenticatedLayout title="Entry Logs">
            <template #header>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">
                        Entry Logs
                    </h2>
                </div>
            </template>

            <EntryLogs :entryLogs="entryLogs" :filters="filters" />
        </AuthenticatedLayout>
    </div>
</template>

<script>
export default {
    props: {
        entryLogs: Object,
        filters: Object,
    }
};
</script>
