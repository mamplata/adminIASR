<template>
    <DaisyTable :data="devices.data" :currentPage="devices.current_page" :lastPage="devices.last_page"
        :excluded-columns="['id']" @change-page="changePage">
        <!-- Custom rendering for STATUS column -->
        <template #cell-status="{ row }">
            <span v-if="row.status === 'active'" class="badge badge-success gap-2">
                ✅ Active
            </span>
            <span v-else class="badge badge-error gap-2">
                ❌ Inactive
            </span>
        </template>

        <template #actions="{ row }">
            <button @click="emit('edit-device', row)" class="btn btn-primary text-white mr-2">
                Edit
            </button>
            <button @click="emit('delete-device', row.id)" class="btn btn-error text-white">
                Delete
            </button>
        </template>
    </DaisyTable>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import DaisyTable from '@/Components/Daisy/DaisyTable.vue';

const props = defineProps({
    devices: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['delete-device', 'change-page', 'edit-device']);

function changePage(page) {
    emit('change-page', page);
}
</script>

<style scoped>
.badge-success {
    @apply inline-flex items-center justify-center bg-green-100 text-green-700 px-4 py-2 rounded-full min-w-[100px] sm:min-w-[120px] md:min-w-[140px] text-sm sm:text-base whitespace-nowrap;
}

.badge-error {
    @apply inline-flex items-center justify-center bg-red-100 text-red-700 px-4 py-2 rounded-full min-w-[100px] sm:min-w-[120px] md:min-w-[140px] text-sm sm:text-base whitespace-nowrap;
}
</style>
