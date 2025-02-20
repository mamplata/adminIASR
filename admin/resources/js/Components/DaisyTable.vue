<template>
    <div class="overflow-x-auto">
        <table class="table w-full">
            <!-- Table Head -->
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-800">
                    <th v-for="column in columns" :key="column" class="text-left p-2">
                        {{ formatHeader(column) }}
                    </th>
                    <th v-if="actionsSlot" class="text-left p-2">Actions</th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
                <tr v-for="(row, index) in data" :key="index" class="border-b dark:border-gray-700">
                    <td v-for="column in columns" :key="column" class="p-2">{{ row[column] }}</td>
                    <td v-if="actionsSlot" class="p-2">
                        <slot name="actions" :row="row"></slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: {
        data: {
            type: Array,
            required: true
        }
    },
    computed: {
        columns() {
            return this.data.length > 0 ? Object.keys(this.data[0]) : [];
        },
        actionsSlot() {
            return !!this.$slots.actions;
        }
    },
    methods: {
        formatHeader(header) {
            return header.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
        }
    }
};
</script>
