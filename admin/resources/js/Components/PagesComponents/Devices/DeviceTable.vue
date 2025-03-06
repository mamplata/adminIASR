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
import DaisyTable from '@/Components/DaisyTable.vue';

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
    @apply bg-green-100 text-green-700 px-3 py-1 rounded-full;
}

.badge-error {
    @apply bg-red-100 text-red-700;
}
</style>
