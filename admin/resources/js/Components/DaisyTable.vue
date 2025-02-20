<template>
    <div class="overflow-x-auto" :class="{'dark': isDarkMode}">
        <table class="table w-full">
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
                <tr v-for="(row, index) in currentPageData" :key="index" class="border-b dark:border-gray-700 text-black dark:text-white">
                    <td v-for="column in columns" :key="column" class="p-2">{{ row[column] }}</td>
                    <td v-if="actionsSlot" class="p-2">
                        <slot name="actions" :row="row"></slot>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="flex justify-end mt-4 space-x-2">
            <button :disabled="currentPage <= 1" @click="changePage(currentPage - 1)" class="btn btn-sm dark:bg-gray-700 dark:text-white">Prev</button>

            <!-- Page Number Buttons -->
            <button v-for="page in pages" :key="page"
                :class="{ 'btn': true, 'btn-sm': true, 'btn-primary': currentPage === page, 'dark:bg-gray-700 dark:text-white': isDarkMode }" 
                @click="changePage(page)">
                {{ page }}
            </button>

            <button :disabled="currentPage >= lastPage" @click="changePage(currentPage + 1)"
                class="btn btn-sm dark:bg-gray-700 dark:text-white">Next</button>
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
            const pageNumbers = [];
            for (let i = 1; i <= this.lastPage; i++) {
                pageNumbers.push(i);
            }
            return pageNumbers;
        },
        currentPageData() {
            const startIndex = (this.currentPage - 1) * 10;  // Adjust based on your per-page setting
            return this.data.slice(startIndex, startIndex + 10);  // Limit to 10 items per page
        }
    },
    methods: {
        formatHeader(header) {
            return header.replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());
        },
        changePage(page) {
            this.$emit('change-page', page);  // Emit the page change event
        },
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
            localStorage.setItem('darkMode', this.isDarkMode);
        }
    }
};
</script>
