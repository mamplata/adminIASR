<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Audit Logs</h2>

        <AuditLogFilterPanel v-model:selectedAction="selectedAction" v-model:selectedType="selectedType"
            v-model:selectedAdmin="selectedAdmin" v-model:startDate="startDate" v-model:endDate="endDate"
            v-model:searchDetails="searchDetails" :actions="actions" :types="types" :admins="admins"
            @search="fetchLogs(1)" @reset="resetSearch" :loading="loading" />

        <DaisyExportModule :data="auditLogs.data" fileName="audit-logs" />
        <DaisyTable :data="auditLogs.data" :currentPage="auditLogs.current_page" :lastPage="auditLogs.last_page"
            @change-page="fetchLogs" :excludedColumns="['type_id']">
            <template #cell-details="{ row }">
                <div>
                    <p class="border rounded border-gray-500 p-1 text-justify">
                        <code v-html="formattedDetails(row.details)"></code>
                    </p>
                </div>
            </template>
        </DaisyTable>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AuditLogFilterPanel from './AuditLogFilterPanel.vue';
import DaisyTable from '@/Components/Daisy/DaisyTable.vue';
import DaisyExportModule from '@/Components/Daisy/DaisyExportModule.vue';

const { props: page } = usePage();

const props = defineProps({
    auditLogs: Object,
    admins: Array,
    actions: Array,
    types: Array,
});

// Filter states
const selectedAction = ref(page.action || '');
const selectedType = ref(page.type || '');
const selectedAdmin = ref(page.admin_name || '');
const startDate = ref(page.start_date || '');
const searchDetails = ref(page.searchDetails || '');
const endDate = ref(page.end_date || '');
const loading = ref(false);


// Function to capitalize the first letter of each key
const capitalizeFirstLetter = (string) => {
    return string.charAt(0).toUpperCase() + string.slice(1);
};

// Computed property to format the JSON data properly
const formattedDetails = (details) => {
    if (!details) return "No details available";

    let parsedDetails = details;

    if (typeof parsedDetails === "string") {
        try {
            parsedDetails = JSON.parse(parsedDetails);
        } catch (error) {
            console.error("Invalid JSON format:", error);
            return "Invalid data format";
        }
    }

    return Object.entries(parsedDetails)
        .map(([key, value]) => {
            const formattedKey = capitalizeFirstLetter(key);
            if (typeof value === "object" && value !== null) {
                const jsonString = JSON.stringify(value, null, 2);
                const truncated = jsonString.length > 200 ? jsonString.slice(0, 200) + '...' : jsonString;
                return `<strong>${formattedKey}:</strong> ${truncated}`;
            }
            const truncated = value.length > 200 ? value.slice(0, 200) + '...' : value;
            return `<strong>${formattedKey}:</strong> ${truncated}`;
        })
        .join("<br>");
};

// Fetch logs using the current filter values
function fetchLogs(page) {

    console.log(searchDetails.value);
    loading.value = true; // Start loading

    const filters = {
        action: selectedAction.value,
        type: selectedType.value,
        admin_name: selectedAdmin.value,
        start_date: startDate.value || null,
        end_date: endDate.value || null,
        searchDetails: searchDetails.value || null,
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
    selectedType.value = '';
    selectedAdmin.value = '';
    startDate.value = '';
    endDate.value = '';
    searchDetails.value = '';
    fetchLogs(1);
}
</script>

<style scoped>
.selected-details {
    white-space: pre-wrap;
    word-wrap: break-word;
    border-radius: 8px;
    padding: 6px;
}
</style>
