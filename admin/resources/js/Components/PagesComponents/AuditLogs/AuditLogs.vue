<template>
    <div class="p-6 overflow-hidden bg-white rounded-md shadow-md dark:bg-dark-eval-1">
        <h2 class="text-lg font-semibold mb-4">Audit Logs</h2>

        <AuditLogFilterPanel v-model:selectedAction="selectedAction" v-model:selectedModel="selectedModel"
            v-model:selectedAdmin="selectedAdmin" v-model:startDate="startDate" v-model:endDate="endDate"
            :actions="actions" :models="models" :admins="admins" @search="onSearch" @reset="onReset" />

        <DaisyTable :data="auditLogs.data" :currentPage="auditLogs.current_page" :lastPage="auditLogs.last_page"
            @change-page="fetchLogs" excluded-columns="[details]">
            <template #actions="{ row }">
                <button class="btn btn-primary" @click="showDetails(row.details)">
                    View Details
                </button>
            </template>
        </DaisyTable>

        <!-- Modal: rendered only if a details value is selected -->
        <DaisyCard v-if="selectedDetails" @close="selectedDetails = null">
            <div>
                <h4 class="font-semibold text-gray-800 dark:text-white mb-2">
                    Audit Log Details
                </h4>
                <p>{{ selectedDetails }}</p>
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
const resetting = ref(false);

// For modal: stores details for the selected row
const selectedDetails = ref(null);

// Opens the modal with the provided details
function showDetails(details) {
    selectedDetails.value = details;
}

// Fetch logs using the current filter values
function fetchLogs(page) {
    if (
        !selectedAction.value &&
        !selectedModel.value &&
        !selectedAdmin.value &&
        !startDate.value &&
        !endDate.value &&
        !resetting.value
    ) {
        noData.value = true;
        return;
    }
    noData.value = false;

    const filters = {
        action: selectedAction.value,
        model: selectedModel.value,
        admin_id: selectedAdmin.value,
        start_date: startDate.value || null,
        end_date: endDate.value || null,
        page: page,
    };

    router
        .get(route('logs.audit-logs.index'), filters, { preserveState: true })
        .then((response) => {
            if (response.props.auditLogs.data.length === 0) {
                noData.value = true;
            }
        });
}

function onSearch() {
    fetchLogs(1);
}

function onReset() {
    selectedAction.value = '';
    selectedModel.value = '';
    selectedAdmin.value = '';
    startDate.value = '';
    endDate.value = '';
    noData.value = false;
    resetting.value = true;
    fetchLogs(1);
    resetting.value = false;
}
</script>
