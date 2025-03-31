<template>
    <div>
        <div class="overflow-x-auto">
            <table v-if="data.length > 0"
                class="table w-full bg-white dark:bg-gray-900 shadow-md rounded-lg text-center">
                <!-- Table Head -->
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-b">
                        <th v-for="column in columns" :key="column" class="px-4 py-2 font-semibold uppercase">
                            {{ formatHeader(column) }}
                        </th>
                        <th v-if="actionsSlot" class="px-4 py-2 font-semibold uppercase">
                            Actions
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <tr v-for="(row, index) in data" :key="index"
                        class="border-b dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                        <td v-for="column in columns" :key="column" class="px-4 py-2 whitespace-normal break-words">
                            <slot :name="`cell-${column}`" :value="row[column]" :row="row">
                                {{ row[column] }}
                            </slot>
                        </td>
                        <td v-if="actionsSlot" class="px-4 py-2">
                            <slot name="actions" :row="row"></slot>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Message when Table is Empty -->
            <div v-else class="text-center py-4 text-gray-500 dark:text-gray-300">
                No data available.
            </div>
        </div>

        <!-- Pagination Controls -->
        <div v-if="lastPage > 1" class="flex justify-end mt-4 space-x-2">
            <!-- First Page Button -->
            <button type="button" :disabled="currentPage === 1" @click="changePage(1)"
                class="btn btn-sm px-3 py-1 transition-colors duration-150 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-white hover:dark:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                First
            </button>

            <!-- Prev Button -->
            <button type="button" :disabled="currentPage <= 1" @click="changePage(currentPage - 1)"
                class="btn btn-sm px-3 py-1 transition-colors duration-150 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-white hover:dark:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                Prev
            </button>

            <!-- Page Number Buttons -->
            <button type="button" v-for="page in pages" :key="page" @click="changePage(page)" :class="[
                'btn btn-sm px-3 py-1 transition-colors duration-150 rounded-md',
                currentPage === page
                    ? 'bg-green-600 text-white shadow-lg hover:bg-green-700'
                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-white hover:dark:bg-gray-600'
            ]">
                {{ page }}
            </button>

            <!-- Next Button -->
            <button type="button" :disabled="currentPage >= lastPage" @click="changePage(currentPage + 1)"
                class="btn btn-sm px-3 py-1 transition-colors duration-150 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-white hover:dark:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                Next
            </button>

            <!-- Last Page Button -->
            <button type="button" :disabled="currentPage === lastPage" @click="changePage(lastPage)"
                class="btn btn-sm px-3 py-1 transition-colors duration-150 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-white hover:dark:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                Last
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useSlots } from 'vue';

// Define Props
const props = defineProps({
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
    },
    excludedColumns: {
        type: Array,
        default: () => []
    }
});

// Define Emit
const emit = defineEmits(['change-page']);

// Access Slots
const slots = useSlots();

// Computed: Columns
const columns = computed(() => {
    if (props.data.length > 0) {
        return Object.keys(props.data[0]).filter(
            column => column !== 'id' && !props.excludedColumns.includes(column)
        );
    }
    return [];
});

// Computed: Check if actions slot exists
const actionsSlot = computed(() => !!slots.actions);

// Computed: Calculate page numbers (groups of 5)
const pages = computed(() => {
    const groupSize = 5;
    const group = Math.floor((props.currentPage - 1) / groupSize);
    const startPage = group * groupSize + 1;
    let endPage = startPage + groupSize - 1;
    if (endPage > props.lastPage) {
        endPage = props.lastPage;
    }
    return Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
});

// Method: Format header string
const formatHeader = header => {
    return header.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

// Method: Change page and emit event
const changePage = page => {
    emit('change-page', page);
};
</script>
