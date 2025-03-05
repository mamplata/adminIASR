<template>
    <!-- Mobile Layout: visible on screens smaller than sm -->
    <div class="sm:hidden mb-2">
        <div class="flex flex-col space-y-2">
            <div>
                <input :value="modelValue" @input="onInput" type="text"
                    placeholder="Search by Desktop name or short_code"
                    class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <select :value="status" @change="onStatusChange"
                    class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button @click="emitSearch" :disabled="loading"
                    class="flex-1 btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Search</span>
                </button>
                <button @click="emitReset" class="flex-1 btn text-white btn-neutral shadow-lg hover:bg-gray-400">
                    Reset
                </button>
            </div>
            <div>
                <button @click="emitAddDevice" class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                    Register Device
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Layout: visible on screens sm and larger -->
    <div class="hidden sm:block">
        <div class="flex items-center mb-2 space-x-2">
            <input :value="modelValue" @input="onInput" type="text" placeholder="Search by Desktop name or short_code"
                class="w-full flex-grow p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <select :value="status" @change="onStatusChange"
                class="w-48 p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button @click="emitSearch" :disabled="loading"
                class="btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Search</span>
            </button>
            <button @click="emitReset" class="btn text-white btn-neutral shadow-lg hover:bg-gray-400">
                Reset
            </button>
        </div>
        <button @click="emitAddDevice" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
            Register Device
        </button>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    status: {
        type: String,
        default: ''
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'update:status', 'search', 'reset', 'add-device']);

// Emit event to update parent’s v-model whenever input changes
function onInput(event) {
    emit('update:modelValue', event.target.value);
}

function onStatusChange(event) {
    emit('update:status', event.target.value);
}

function emitSearch() {
    emit('search');
}

function emitReset() {
    emit('reset');
}

function emitAddDevice() {
    emit('add-device');
}
</script>
