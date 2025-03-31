<template>
    <div class="export-module">
        <button @click="exportCSV" class="btn btn-primary">Export as CSV</button>
        <button @click="exportExcel" class="btn btn-secondary">Export as Excel</button>
    </div>
</template>

<script setup>
import * as XLSX from 'xlsx';
import { defineProps } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true
    },
    fileName: {
        type: String,
        default: 'export'
    }
});

// Function to export data as CSV
const exportCSV = () => {
    const csvContent = convertToCSV(props.data);
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `${props.fileName}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Helper function to convert JSON array to CSV string
function convertToCSV(objArray) {
    if (!objArray.length) return '';
    const keys = Object.keys(objArray[0]);
    const header = keys.join(',') + '\r\n';
    const rows = objArray.map(item => keys.map(key => {
        // Escape quotes and commas if needed
        const value = item[key] !== null && item[key] !== undefined ? item[key] : '';
        return `"${value.toString().replace(/"/g, '""')}"`;
    }).join(',')).join('\r\n');
    return header + rows;
}

// Function to export data as Excel using the XLSX library
const exportExcel = () => {
    // Create a new workbook and worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(props.data);
    // Append the worksheet to the workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    // Save the workbook to a file
    XLSX.writeFile(wb, `${props.fileName}.xlsx`);
};
</script>

<style scoped>
.export-module {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    cursor: pointer;
    border: none;
    border-radius: 4px;
}

.btn-primary {
    background-color: #3b82f6;
    color: #fff;
}

.btn-secondary {
    background-color: #10b981;
    color: #fff;
}
</style>
