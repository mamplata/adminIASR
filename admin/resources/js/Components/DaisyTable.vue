<template>
    <div class="overflow-x-auto">
        <table v-if="data.length > 0" class="table w-full">
            <!-- Table Head -->
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-800 text-black dark:text-white">
                    <th v-for="column in columns" :key="column" class="text-left p-2">
                        {{ formatHeader(column) }}
                    </th>
                    <th v-if="actionsSlot" class="text-left p-2">Actions</th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
                <tr v-for="(row, index) in data" :key="index"
                    class="border-b dark:border-gray-700 text-black dark:text-white">
                    <td v-for="column in columns" :key="column" class="p-2">{{ row[column] }}</td>
                    <td v-if="actionsSlot" class="p-2">
                        <slot name="actions" :row="row"></slot>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Message when Table is Empty -->
        <div v-else class="text-center py-4 text-gray-500 dark:text-gray-300">
            No data available.
        </div>

        <!-- Pagination Controls -->
        <div v-if="lastPage > 1" class="flex justify-end mt-4 space-x-2">
            <button :disabled="currentPage <= 1" @click="changePage(currentPage - 1)"
                class="btn btn-sm dark:bg-gray-700 dark:text-white">
                Prev
            </button>

            <!-- Page Number Buttons -->
            <button v-for="page in pages" :key="page" :class="{
                'btn': true,
                'btn-sm': true,
                'text-white btn-success shadow-lg hover:bg-[#20714c]': currentPage === page,
                'dark:bg-gray-700 dark:text-white': isDarkMode
            }" @click="changePage(page)">
                {{ page }}
            </button>

            <button :disabled="currentPage >= lastPage" @click="changePage(currentPage + 1)"
                class="btn btn-sm dark:bg-gray-700 dark:text-white">
                Next
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        data: {
            type: Array,
            required: true
        },
        currentPage: {
            type: Number,
            required: true
        },
        lastPage: {
            type: Number,
            required: true
        }
    },
    computed: {
        columns() {
            return this.data.length > 0 ? Object.keys(this.data[0]) : [];
        },
        actionsSlot() {
            return !!this.$slots.actions;
        },
        pages() {
            return Array.from({ length: this.lastPage }, (_, i) => i + 1);
        }
    },
    methods: {
        formatHeader(header) {
            return header.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
        },
        changePage(page) {
            this.$emit('change-page', page);  // Emit the new page number to parent
        }
    }
};
</script>
