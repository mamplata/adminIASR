<template>
    <DaisyTable :data="announcements.data" :excludedColumns="['id', 'content']"
        :currentPage="announcements.current_page" :lastPage="announcements.last_page" @change-page="onChangePage">
        <template #actions="{ row }">
            <div class="flex gap-2">
                <button @click="view(row)" class="btn text-white btn-info shadow-lg hover:bg-blue-700 mb-2">
                    View
                </button>
                <button @click="edit(row)" class="btn text-white btn-warning shadow-lg hover:bg-yellow-700 mb-2">
                    Edit
                </button>
                <button @click="confirmDelete(row.id)" class="btn text-white btn-error shadow-lg hover:bg-red-700 mb-2">
                    Delete
                </button>
            </div>
        </template>
    </DaisyTable>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import DaisyTable from '@/Components/DaisyTable.vue';

const props = defineProps({
    announcements: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['change-page', 'view', 'edit', 'delete']);

function onChangePage(page) {
    emit('change-page', page);
}

function view(row) {
    emit('view', row);
}

function edit(row) {
    emit('edit', row);
}

function confirmDelete(id) {
    emit('delete', id);
}
</script>
