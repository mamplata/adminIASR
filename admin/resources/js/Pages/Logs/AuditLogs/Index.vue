<script setup>
import AuditLogsManagement from '@/Components/PagesComponents/AuditLogsManagement.vue';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { useForm, router } from '@inertiajs/vue3';
</script>

<template>
    <div>
        <AuthenticatedLayout title="Audit Logs">
            <template #header>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">
                        Audit Logs
                    </h2>
                </div>
            </template>

            <!-- Passing the users prop to the UserManagement component -->
            <AuditLogsManagement 
                :auditLogs="auditLogs"
                :filters="filters"
                :admins="admins"
                :actions="actions"
                :models="models"
            />
        </AuthenticatedLayout>
    </div>
</template>

<script>
export default {
    props: { 
        auditLogs: Object, 
        filters: Object, 
        admins: Array, 
        actions: Array, 
        models: Array 
    }
};
</script>
