<template>
    <DaisyTable :data="devices.data" :currentPage="devices.current_page" :lastPage="devices.last_page"
        @change-page="changePage" :excluded-columns="['id']">
        <!-- Actions slot for each device row -->
        <template #actions="{ row }">
            <button @click="() => emit('delete-device', row.id)" class="btn btn-error text-white">
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
        required: true
    }
});

const emit = defineEmits(['delete-device', 'change-page']);

function changePage(page) {
    emit('change-page', page);
}
</script>
