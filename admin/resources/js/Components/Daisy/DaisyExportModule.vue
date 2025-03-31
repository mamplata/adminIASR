<template>
    <div class="export-module flex gap-4 mt-4">
        <!-- CSV export button using image icon -->
        <button @click="handleExportCSV" class="btn btn-soft" title="Export as CSV">
            <span>Export as CSV</span> <img :src="csv" alt="CSV Export" class="w-6 h-6" />
        </button>
        <!-- Excel export button using image icon -->
        <button @click="handleExportExcel" class="btn btn-soft" title="Export as Excel">
            <span>Export as Excel</span> <img :src="excel" alt="Excel Export" class="w-6 h-6" />
        </button>
    </div>
</template>

<script setup>
import * as XLSX from 'xlsx';
import { ref } from 'vue';
import { defineProps } from 'vue';
import csv from '@/../assets/img/csv.png';
import excel from '@/../assets/img/excel.png';

const props = defineProps({
    exportUrl: {
        type: String,
        required: true
    },
    fileName: {
        type: String,
        default: 'export'
    }
});

const exportData = ref([]);

// ------------------ Data Fetching ------------------

const fetchExportData = async () => {
    try {
        const response = await fetch(props.exportUrl);
        const json = await response.json();

        if (Array.isArray(json)) {
            exportData.value = json;
        } else {
            let foundArray = false;
            for (const key in json) {
                if (Array.isArray(json[key])) {
                    exportData.value = json[key];
                    foundArray = true;
                    break;
                }
            }
            if (!foundArray) {
                exportData.value = [];
            }
        }
    } catch (error) {
        console.error('Failed to fetch export data:', error);
    }
};

// ------------------ Date and Title Helpers ------------------

function getTitle(fileName) {
    return fileName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function getFormattedDate() {
    const now = new Date();
    const month = now.toLocaleDateString('en-US', { month: 'long' });
    const day = now.toLocaleDateString('en-US', { day: 'numeric' });
    const year = now.toLocaleDateString('en-US', { year: 'numeric' });
    const weekday = now.toLocaleDateString('en-US', { weekday: 'long' });
    return `${month} ${day}, ${year} (${weekday})`;
}

function getHeaderText() {
    const title = getTitle(props.fileName);
    const dateStr = getFormattedDate();
    return `${title} as of ${dateStr}`;
}

// ------------------ Totals Computation ------------------

/**
 * Returns the ISO week number for a given date.
 * @param {Date} date 
 * @returns {number} ISO week number
 */
function getISOWeek(date) {
    const target = new Date(date.valueOf());
    const dayNr = (date.getDay() + 6) % 7;
    target.setDate(target.getDate() - dayNr + 3);
    const firstThursday = target.valueOf();
    target.setMonth(0, 1);
    if (target.getDay() !== 4) {
        target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
    }
    const weekNo = 1 + Math.ceil((firstThursday - target) / 604800000);
    return weekNo;
}

/**
 * Computes totals grouped by day, week, and year based on each record's timestamp.
 * If the data includes a 'time_type' column (determined by the first record), separate counts for 'IN' and 'OUT' are computed.
 */
function computeTotals(data) {
    const dayTotals = {};
    const weekTotals = {};
    const yearTotals = {};
    let overallTotal = data.length;

    // Check if the "time_type" column exists in the data
    const hasTimeType = data.length > 0 && Object.keys(data[0]).includes('time_type');

    // Totals by time_type (only if column exists)
    const dayTotalsIn = {};
    const dayTotalsOut = {};
    const weekTotalsIn = {};
    const weekTotalsOut = {};
    const yearTotalsIn = {};
    const yearTotalsOut = {};
    let overallTotalIn = 0;
    let overallTotalOut = 0;

    data.forEach(item => {
        if (item.timestamp) {
            const date = new Date(item.timestamp);
            const dayKey = date.toISOString().split('T')[0];
            dayTotals[dayKey] = (dayTotals[dayKey] || 0) + 1;

            const year = date.getFullYear();
            const week = getISOWeek(date);
            const weekKey = `${year}-W${week.toString().padStart(2, '0')}`;
            weekTotals[weekKey] = (weekTotals[weekKey] || 0) + 1;

            const yearKey = year.toString();
            yearTotals[yearKey] = (yearTotals[yearKey] || 0) + 1;

            if (hasTimeType && item.time_type) {
                const tt = item.time_type.toString().toUpperCase();
                if (tt === 'IN') {
                    dayTotalsIn[dayKey] = (dayTotalsIn[dayKey] || 0) + 1;
                    weekTotalsIn[weekKey] = (weekTotalsIn[weekKey] || 0) + 1;
                    yearTotalsIn[yearKey] = (yearTotalsIn[yearKey] || 0) + 1;
                    overallTotalIn++;
                } else if (tt === 'OUT') {
                    dayTotalsOut[dayKey] = (dayTotalsOut[dayKey] || 0) + 1;
                    weekTotalsOut[weekKey] = (weekTotalsOut[weekKey] || 0) + 1;
                    yearTotalsOut[yearKey] = (yearTotalsOut[yearKey] || 0) + 1;
                    overallTotalOut++;
                }
            }
        }
    });

    return {
        dayTotals,
        weekTotals,
        yearTotals,
        overallTotal,
        hasTimeType,
        dayTotalsIn,
        dayTotalsOut,
        weekTotalsIn,
        weekTotalsOut,
        yearTotalsIn,
        yearTotalsOut,
        overallTotalIn,
        overallTotalOut
    };
}

// ------------------ CSV Export ------------------

const handleExportCSV = async () => {
    await fetchExportData();
    const timestamp = getTimestamp();
    const csvContent = convertToCSV(exportData.value);
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

function convertToCSV(objArray) {
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
            return `"${value.toString().replace(/"/g, '""')}"`;
        }).join(',');
        csvRows.push(row);
    });

    // Append Totals
    const totals = computeTotals(objArray);
    csvRows.push(''); // Blank row before totals

    // Overall Totals by Day, Week, Year
    csvRows.push('Totals Per Day:');
    csvRows.push('Date,Count');
    Object.keys(totals.dayTotals).sort().forEach(day => {
        csvRows.push(`${day},${totals.dayTotals[day]}`);
    });

    csvRows.push('');
    csvRows.push('Totals Per Week:');
    csvRows.push('Week,Count');
    Object.keys(totals.weekTotals).sort().forEach(week => {
        csvRows.push(`${week},${totals.weekTotals[week]}`);
    });

    csvRows.push('');
    csvRows.push('Totals Per Year:');
    csvRows.push('Year,Count');
    Object.keys(totals.yearTotals).sort().forEach(year => {
        csvRows.push(`${year},${totals.yearTotals[year]}`);
    });

    csvRows.push('');
    csvRows.push(`Overall Total:,${totals.overallTotal}`);

    // Append IN/OUT breakdown only if "time_type" column exists
    if (totals.hasTimeType) {
        csvRows.push('');
        csvRows.push('Time Type Totals Per Day:');
        csvRows.push('Date,IN,OUT');
        Object.keys(totals.dayTotals).sort().forEach(day => {
            const inCount = totals.dayTotalsIn[day] || 0;
            const outCount = totals.dayTotalsOut[day] || 0;
            csvRows.push(`${day},${inCount},${outCount}`);
        });

        csvRows.push('');
        csvRows.push('Time Type Totals Per Week:');
        csvRows.push('Week,IN,OUT');
        Object.keys(totals.weekTotals).sort().forEach(week => {
            const inCount = totals.weekTotalsIn[week] || 0;
            const outCount = totals.weekTotalsOut[week] || 0;
            csvRows.push(`${week},${inCount},${outCount}`);
        });

        csvRows.push('');
        csvRows.push('Time Type Totals Per Year:');
        csvRows.push('Year,IN,OUT');
        Object.keys(totals.yearTotals).sort().forEach(year => {
            const inCount = totals.yearTotalsIn[year] || 0;
            const outCount = totals.yearTotalsOut[year] || 0;
            csvRows.push(`${year},${inCount},${outCount}`);
        });

        csvRows.push('');
        csvRows.push('Overall Totals by Time Type:');
        csvRows.push('IN,OUT');
        csvRows.push(`${totals.overallTotalIn},${totals.overallTotalOut}`);
    }

    return csvRows.join('\r\n');
}

// ------------------ Excel Export ------------------

const handleExportExcel = async () => {
    await fetchExportData();
    const timestamp = getTimestamp();
    let wsData = [];

    wsData.push([getHeaderText()]);
    wsData.push([]);

    if (exportData.value.length > 0) {
        const keys = Object.keys(exportData.value[0]);
        wsData.push(keys);
        exportData.value.forEach(item => {
            wsData.push(keys.map(key => item[key] !== null && item[key] !== undefined ? item[key] : ''));
        });
    }

    wsData.push([]);
    const totals = computeTotals(exportData.value);

    wsData.push(['Totals Per Day:']);
    wsData.push(['Date', 'Count']);
    Object.keys(totals.dayTotals).sort().forEach(day => {
        wsData.push([day, totals.dayTotals[day]]);
    });

    wsData.push([]);
    wsData.push(['Totals Per Week:']);
    wsData.push(['Week', 'Count']);
    Object.keys(totals.weekTotals).sort().forEach(week => {
        wsData.push([week, totals.weekTotals[week]]);
    });

    wsData.push([]);
    wsData.push(['Totals Per Year:']);
    wsData.push(['Year', 'Count']);
    Object.keys(totals.yearTotals).sort().forEach(year => {
        wsData.push([year, totals.yearTotals[year]]);
    });

    wsData.push([]);
    wsData.push(['Overall Total:', totals.overallTotal]);

    if (totals.hasTimeType) {
        wsData.push([]);
        wsData.push(['Time Type Totals Per Day:']);
        wsData.push(['Date', 'IN', 'OUT']);
        Object.keys(totals.dayTotals).sort().forEach(day => {
            const inCount = totals.dayTotalsIn[day] || 0;
            const outCount = totals.dayTotalsOut[day] || 0;
            wsData.push([day, inCount, outCount]);
        });

        wsData.push([]);
        wsData.push(['Time Type Totals Per Week:']);
        wsData.push(['Week', 'IN', 'OUT']);
        Object.keys(totals.weekTotals).sort().forEach(week => {
            const inCount = totals.weekTotalsIn[week] || 0;
            const outCount = totals.weekTotalsOut[week] || 0;
            wsData.push([week, inCount, outCount]);
        });

        wsData.push([]);
        wsData.push(['Time Type Totals Per Year:']);
        wsData.push(['Year', 'IN', 'OUT']);
        Object.keys(totals.yearTotals).sort().forEach(year => {
            const inCount = totals.yearTotalsIn[year] || 0;
            const outCount = totals.yearTotalsOut[year] || 0;
            wsData.push([year, inCount, outCount]);
        });

        wsData.push([]);
        wsData.push(['Overall Totals by Time Type:']);
        wsData.push(['IN', 'OUT']);
        wsData.push([totals.overallTotalIn, totals.overallTotalOut]);
    }

    const ws = XLSX.utils.aoa_to_sheet(wsData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    XLSX.writeFile(wb, `${props.fileName}-${timestamp}.xlsx`);
};

// ------------------ Timestamp Helper ------------------

function getTimestamp() {
    return new Date().toISOString().replace(/[-:]/g, '').replace(/\..+/, '');
}
</script>

<style scoped>
/* Your styles here */
</style>
