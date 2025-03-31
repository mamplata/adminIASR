<template>
    <div class="flex gap-4 mt-4">
        <!-- CSV export button using image icon -->
        <button @click="exportCSV" class="btn btn-primary" title="Export as CSV">
            <img :src="csv" alt="CSV Export" class="w-6 h-6" />
        </button>
        <!-- Excel export button using image icon -->
        <button @click="exportExcel" class="btn btn-secondary" title="Export as Excel">
            <img :src="excel" alt="Excel Export" class="w-6 h-6" />
        </button>
    </div>
</template>

<script setup>
import * as XLSX from 'xlsx';
import { defineProps } from 'vue';
import csv from '@/../assets/img/csv.png';
import excel from '@/../assets/img/excel.png';

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

// Returns a timestamp string formatted as YYYYMMDDTHHMMSS
function getTimestamp() {
    return new Date().toISOString().replace(/[-:]/g, '').replace(/\..+/, '');
}

// Function to export data as CSV
const exportCSV = () => {
    const timestamp = getTimestamp();
    const csvContent = convertToCSV(props.data);
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `${props.fileName}-${timestamp}.csv`);
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
    const rows = objArray
        .map(item =>
            keys
                .map(key => {
                    const value = item[key] !== null && item[key] !== undefined ? item[key] : '';
                    return `"${value.toString().replace(/"/g, '""')}"`;
                })
                .join(',')
        )
        .join('\r\n');
    return header + rows;
}

// Function to export data as Excel using the XLSX library
const exportExcel = () => {
    const timestamp = getTimestamp();
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.json_to_sheet(props.data);
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    XLSX.writeFile(wb, `${props.fileName}-${timestamp}.xlsx`);
};
</script>
