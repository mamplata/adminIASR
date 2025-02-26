<template>
    <DaisyTable :data="devices.data" :currentPage="devices.current_page" :lastPage="devices.last_page"
        @change-page="changePage" :excluded-columns="['id']">
        <!-- Actions slot for each device row -->
        <template #actions="{ row }">
            <button @click="$emit('edit-device', row)" class="btn btn-info text-white mr-2">
                Edit
            </button>
            <button @click="$emit('delete-device', row.id)" class="btn btn-error text-white">
                Delete
            </button>
        </template>
    </DaisyTable>
</template>

<script>
import DaisyTable from '@/Components/DaisyTable.vue';

export default {
    name: 'DeviceTable',
    components: { DaisyTable },
    props: {
        devices: {
            type: Object,
            required: true
        }
    },
    methods: {
        changePage(page) {
            this.$emit('change-page', page);
        }
    }
};
</script>
