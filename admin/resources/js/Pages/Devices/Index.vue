<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Devices from '@/Components/PagesComponents/Devices/Devices.vue';
</script>

<template>
    <div>
        <AuthenticatedLayout title="Devices">
            <template #header>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-xl font-semibold leading-tight">
                        Devices
                    </h2>
                </div>
            </template>

            <!-- Passing the users prop to the UserManagement component -->
            <Devices :devices="devices" />
        </AuthenticatedLayout>
    </div>
</template>

<script>
export default {
    props: { devices: Object },
};
</script>
