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

        <!-- Modal: rendered only if a details value is selected -->
        <DaisyCard v-if="selectedDetails" @close="selectedDetails = null">
            <div>
                <h4 class="font-semibold text-gray-800 dark:text-white mb-2">
                    Audit Log Details
                </h4>
                <p class="selected-details"><code> {{ selectedDetails }} </code></p>
            </div>
        </DaisyCard>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuditLogFilterPanel from './AuditLogFilterPanel.vue';
import DaisyTable from '@/Components/DaisyTable.vue';
import DaisyCard from '@/Components/DaisyCard.vue'; // Your modal component

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
