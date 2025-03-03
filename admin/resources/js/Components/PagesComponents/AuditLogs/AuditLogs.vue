<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Audit Logs</h2>

        <AuditLogFilterPanel v-model:selectedAction="selectedAction" v-model:selectedModel="selectedModel"
            v-model:selectedAdmin="selectedAdmin" v-model:startDate="startDate" v-model:endDate="endDate"
            :actions="actions" :models="models" :admins="admins" @search="fetchLogs(1)" @reset="resetSearch" />

        <DaisyTable :data="auditLogs.data" :currentPage="auditLogs.current_page" :lastPage="auditLogs.last_page"
            @change-page="fetchLogs" :excludedColumns="['details']">
            <template #actions="{ row }">
                <button v-if="row.details != null" class="btn btn-info text-white" @click="showDetails(row.details)">
                    View Details
                </button>
                <p v-else>No details</p>
            </template>
        </DaisyTable>

        <DaisyCard v-if="selectedDetails" @close="selectedDetails = null">
            <div>
                <h4 class="font-semibold text-gray-800 dark:text-white mb-2">
                    Audit Log Details
                </h4>
                <p class="selected-details">
                    <code v-html="formattedDetails"></code>
                </p>
            </div>
        </DaisyCard>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuditLogFilterPanel from './AuditLogFilterPanel.vue';
import DaisyTable from '@/Components/DaisyTable.vue';
import DaisyCard from '@/Components/DaisyCard.vue';

const props = defineProps({
    auditLogs: Object,
    admins: Array,
    actions: Array,
    models: Array,
});

// Filter states
const selectedAction = ref('');
const selectedModel = ref('');
const selectedAdmin = ref('');
const startDate = ref('');
const endDate = ref('');
const noData = ref(false);
const loading = ref(false);

// For modal: stores details for the selected row
const selectedDetails = ref(null);

// Opens the modal with the provided details
function showDetails(details) {
    selectedDetails.value = details;
}

// Function to capitalize the first letter of each key
const capitalizeFirstLetter = (string) => {
    return string.charAt(0).toUpperCase() + string.slice(1);
};

// Computed property to format the JSON data properly
const formattedDetails = computed(() => {
    if (!selectedDetails.value) return "";

    let parsedDetails = selectedDetails.value;

    // Ensure selectedDetails is an object and not a string
    if (typeof parsedDetails === "string") {
        try {
            parsedDetails = JSON.parse(parsedDetails);
        } catch (error) {
            console.error("Invalid JSON format:", error);
            return "Invalid data format";
        }
    }

    // Format the JSON object into a readable key-value pair with capitalized keys
    return Object.entries(parsedDetails)
        .map(([key, value]) => {
            const formattedKey = capitalizeFirstLetter(key);

            if (typeof value === "object" && value !== null) {
                // Handle nested objects (e.g., "content" field)
                return `<strong>${formattedKey}:</strong> ${JSON.stringify(value, null, 2)}`;
            }
            return `<strong>${formattedKey}:</strong> ${value}`;
        })
        .join("<br>");
});

// Fetch logs using the current filter values
function fetchLogs(page) {
    loading.value = true; // Start loading

    const filters = {
        action: selectedAction.value,
        model: selectedModel.value,
        admin_id: selectedAdmin.value,
        start_date: startDate.value || null,
        end_date: endDate.value || null,
        page: page,
    };

    router.get(route('logs.audit-logs.index'), filters, {
        preserveState: true,
        onFinish: () => {
            loading.value = false; // Ensure loading is turned off
        }
    });
}


function resetSearch() {
    selectedAction.value = '';
    selectedModel.value = '';
    selectedAdmin.value = '';
    startDate.value = '';
    endDate.value = '';
    fetchLogs(1);
}
</script>

<style scoped>
.selected-details {
    white-space: pre-wrap;
    word-wrap: break-word;
    background-color: rgba(0, 0, 0, 0.4);
    border-radius: 8px;
    padding: 6px;
}
</style>
