<script setup>
import Dashboard from '@/Components/PagesComponents/Dashboard/Dashboard.vue'
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    Dashboard
                </h2>
            </div>
        </template>

        <Dashboard :registeredCardsCount="registeredCardsCount" :entryLogsIn="entryLogsIn" :entryLogsOut="entryLogsOut"
            :unauthorizedLogsIn="unauthorizedLogsIn" :unauthorizedLogsOut="unauthorizedLogsOut"
            :registeredDevicesCount="registeredDevicesCount" :auditLogs="auditLogs" />
    </AuthenticatedLayout>
</template>
<script>
export default {
    props: {
        registeredCardsCount: Number,
        entryLogsIn: Number,
        entryLogsOut: Number,
        unauthorizedLogsIn: Number,
        unauthorizedLogsOut: Number,
        registeredDevicesCount: Number,
        auditLogs: Array
    }
};
</script>
