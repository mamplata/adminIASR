<template>
    <DaisyTable :data="devices.data" :currentPage="devices.current_page" :lastPage="devices.last_page"
        @change-page="onChangePage" :excluded-columns="['id']">
        <template #actions="{ row }">
            <button @click="$emit('edit', row)" class="btn btn-info text-white mr-2">
                Edit
            </button>
            <button @click="$emit('delete', row.id)" class="btn btn-error text-white">
                Delete
            </button>
        </template>
    </DaisyTable>
</template>

<script setup>
import DaisyTable from '@/Components/DaisyTable.vue';

const props = defineProps({
    devices: Object,
});
const emits = defineEmits(['edit', 'delete', 'change-page']);

function onChangePage(page) {
    emits('change-page', page);
}
</script>
