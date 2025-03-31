<template>
    <div class="p-6 bg-base-100 dark:bg-dark-eval-1 rounded-md shadow-md">
        <!-- Welcome Message and Current Date -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold dark:text-white">
                Welcome, {{ $page.props.auth.user.name }}!
            </h1>
            <p class="text-md dark:text-gray-300">
                Today is {{ formatDate(new Date()) }}
            </p>
        </div>

        <!-- First Row: Registered Cards/Students and Registered Devices -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Registered Cards/Students -->
            <div class="rounded-lg">
                <!-- Header -->
                <div class="rounded-t-lg p-4 text-center flex items-center justify-center"
                    style="background-color: #198754;">
                    <span class="mr-2">
                        <CardIcon class="h-6 w-6 text-white" />
                    </span>
                    <h2 class="text-lg font-semibold text-white">
                        Registered Cards/Students
                    </h2>
                </div>
                <!-- Body -->
                <div class="p-6 bg-base-200 dark:bg-base-700 text-center">
                    <p class="text-2xl dark:text-white">{{ registeredCardsCount }}</p>
                </div>
            </div>

            <!-- Registered Devices -->
            <div class="rounded-lg">
                <!-- Header -->
                <div class="rounded-t-lg p-4 text-center flex items-center justify-center"
                    style="background-color: #198754;">
                    <span class="mr-2">
                        <DeviceIcon class="h-6 w-6 text-white" />
                    </span>
                    <h2 class="text-lg font-semibold text-white">
                        Registered Devices
                    </h2>
                </div>
                <!-- Body -->
                <div class="p-6 bg-base-200 dark:bg-base-700 text-center">
                    <p class="text-2xl dark:text-white">{{ registeredDevicesCount }}</p>
                </div>
            </div>
        </div>

        <!-- Second Row: Entry Logs and Unauthorized Logs -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <!-- Entry Logs: Combined Card -->
            <div class="rounded-lg">
                <!-- Header -->
                <div class="rounded-t-lg p-4 text-center flex items-center justify-center"
                    style="background-color: #198754;">
                    <span class="mr-2">
                        <EntryIcon class="h-6 w-6 text-white" />
                    </span>
                    <h2 class="text-lg font-semibold text-white">Entry Logs</h2>
                </div>
                <!-- Body -->
                <div class="p-6 bg-base-200 dark:bg-base-700 text-center">
                    <div class="flex justify-around mt-4">
                        <div>
                            <p class="text-sm dark:text-gray-300">IN</p>
                            <p class="text-xl dark:text-white">{{ entryLogsIn }}</p>
                        </div>
                        <div>
                            <p class="text-sm dark:text-gray-300">OUT</p>
                            <p class="text-xl dark:text-white">{{ entryLogsOut }}</p>
                        </div>
                        <div>
                            <p class="text-sm dark:text-gray-300">TOTAL</p>
                            <p class="text-xl dark:text-white">{{ entryLogsIn + entryLogsOut }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unauthorized Logs: Combined Card -->
            <div class="rounded-lg">
                <!-- Header -->
                <div class="rounded-t-lg p-4 text-center flex items-center justify-center"
                    style="background-color: #198754;">
                    <span class="mr-2">
                        <UnauthorizedIcon class="h-6 w-6 text-white" />
                    </span>
                    <h2 class="text-lg font-semibold text-white">
                        Unauthorized Logs
                    </h2>
                </div>
                <!-- Body -->
                <div class="p-6 bg-base-200 dark:bg-base-700 text-center">
                    <div class="flex justify-around mt-4">
                        <div>
                            <p class="text-sm dark:text-gray-300">IN</p>
                            <p class="text-xl dark:text-white">{{ unauthorizedLogsIn }}</p>
                        </div>
                        <div>
                            <p class="text-sm dark:text-gray-300">OUT</p>
                            <p class="text-xl dark:text-white">{{ unauthorizedLogsOut }}</p>
                        </div>
                        <div>
                            <p class="text-sm dark:text-gray-300">TOTAL</p>
                            <p class="text-xl dark:text-white">{{ unauthorizedLogsIn + unauthorizedLogsOut }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Audit Logs Table -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4 dark:text-white">Recent Audit Logs</h2>
            <div class="overflow-x-auto">
                <table v-if="auditLogs.length > 0" class="table w-full bg-white dark:bg-gray-900 shadow-md rounded-lg">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-b">
                            <th class="px-4 py-2 font-semibold uppercase">Admin Name</th>
                            <th class="px-4 py-2 font-semibold uppercase">Action</th>
                            <th class="px-4 py-2 font-semibold uppercase">Type</th>
                            <th class="px-4 py-2 font-semibold uppercase">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(log, index) in auditLogs" :key="index"
                            class="border-b dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                            <td class="px-4 py-2 whitespace-normal break-words">{{ log.admin_name }}</td>
                            <td class="px-4 py-2 whitespace-normal break-words">{{ log.action }}</td>
                            <td class="px-4 py-2 whitespace-normal break-words">{{ log.type }}</td>
                            <td class="px-4 py-2 truncate">{{ truncate(log.details, 50) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Message when Table is Empty -->
                <div v-else class="text-center py-4 text-gray-500 dark:text-gray-300">
                    No data available.
                </div>
            </div>
        </div>
    </div>
</template>


<script setup>
// Importing pre-defined icons
import { CardIcon, DeviceIcon, UnauthorizedIcon, EntryIcon } from '@/Components/Icons/outline';


const props = defineProps({
    registeredCardsCount: {
        type: Number,
        required: true,
    },
    entryLogsIn: {
        type: Number,
        required: true,
    },
    entryLogsOut: {
        type: Number,
        required: true,
    },
    unauthorizedLogsIn: {
        type: Number,
        required: true,
    },
    unauthorizedLogsOut: {
        type: Number,
        required: true,
    },
    registeredDevicesCount: {
        type: Number,
        required: true,
    },
    auditLogs: {
        type: Array,
        required: true,
    },
});

/**
 * Truncates a given text to a specified character limit and adds an ellipsis.
 * @param {String} text - The text to truncate.
 * @param {Number} limit - Maximum number of characters.
 * @return {String} The truncated text.
 */
function truncate(text, limit) {
    if (!text) return '';
    return text.length > limit ? text.substring(0, limit) + '...' : text;
}

function formatDate(date) {
    const day = date.getDate();
    const year = date.getFullYear();
    const month = date.toLocaleString('default', { month: 'long' });
    const weekday = date.toLocaleString('default', { weekday: 'long' });
    return `${month} ${day}, ${year}, ${weekday}`;
}
</script>
