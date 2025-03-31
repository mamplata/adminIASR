<template>
    <div class="export-module flex gap-4 mt-4">
        <!-- CSV export button using image icon -->
        <button @click="exportCSV" class="btn btn-soft" title="Export as CSV">
            <span>Export as</span> <img :src="csv" alt="CSV Export" class="w-6 h-6" />
        </button>
        <!-- Excel export button using image icon -->
        <button @click="exportExcel" class="btn btn-soft" title="Export as Excel">
            <span>Export as</span> <img :src="excel" alt="Excel Export" class="w-6 h-6" />
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

// Helper: Capitalize words and replace hyphens with spaces
function getTitle(fileName) {
    return fileName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

// Helper: Get formatted date as "March, 31, 2025, Monday"
function getFormattedDate() {
    const now = new Date();
    const month = now.toLocaleDateString('en-US', { month: 'long' });
    const day = now.toLocaleDateString('en-US', { day: 'numeric' });
    const year = now.toLocaleDateString('en-US', { year: 'numeric' });
    const weekday = now.toLocaleDateString('en-US', { weekday: 'long' });
    return `${month}, ${day}, ${year}, ${weekday}`;
}

// Generate header text based on fileName and current date
function getHeaderText() {
    const title = getTitle(props.fileName);
    const dateStr = getFormattedDate();
    return `${title} as of ${dateStr}`;
}

// --------------------- CSV Export ------------------------

// Updated function to include header text in CSV content
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

// Updated helper function to convert JSON array to CSV string with header text
function convertToCSV(objArray) {
    // Generate header text row(s)
    const headerText = getHeaderText();
    let csvRows = [];
    csvRows.push(headerText);
    csvRows.push(''); // Blank row

    if (!objArray.length) {
        return csvRows.join('\r\n');
    }

    // Data headers
    const keys = Object.keys(objArray[0]);
    csvRows.push(keys.join(','));

    // Data rows
    objArray.forEach(item => {
        const row = keys.map(key => {
            const value = item[key] !== null && item[key] !== undefined ? item[key] : '';
            // Escape quotes and commas if necessary
            return `"${value.toString().replace(/"/g, '""')}"`;
        }).join(',');
        csvRows.push(row);
    });

    return csvRows.join('\r\n');
}

// --------------------- Excel Export ------------------------

// Updated exportExcel to include header text rows in Excel file
const exportExcel = () => {
    const timestamp = getTimestamp();
    let wsData = [];

    // First row: header text
    wsData.push([getHeaderText()]);
    // Second row: blank row
    wsData.push([]);

    if (props.data.length > 0) {
        const keys = Object.keys(props.data[0]);
        // Third row: table headers
        wsData.push(keys);
        // Data rows
        props.data.forEach(item => {
            wsData.push(keys.map(key => item[key] !== null && item[key] !== undefined ? item[key] : ''));
        });
    }

    const ws = XLSX.utils.aoa_to_sheet(wsData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    XLSX.writeFile(wb, `${props.fileName}-${timestamp}.xlsx`);
};

// Helper: Generate a timestamp in YYYYMMDDTHHMMSS format
function getTimestamp() {
    return new Date().toISOString().replace(/[-:]/g, '').replace(/\..+/, '');
}
</script>

<style scoped></style>
