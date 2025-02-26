<template>
    <div>
        <!-- Mobile Layout -->
        <div class="sm:hidden mb-2">
            <div class="flex flex-col space-y-2">
                <div>
                    <input v-model="searchInput" type="text" placeholder="Search by device name"
                        class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="flex space-x-2">
                    <button @click="onSearchClick" :disabled="loading"
                        class="flex-1 btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        <span v-else>Search</span>
                    </button>
                    <button @click="onResetClick"
                        class="flex-1 btn text-white btn-secondary shadow-lg hover:bg-gray-400">
                        Reset
                    </button>
                </div>
                <div>
                    <button @click="onAddClick" class="w-full btn text-white btn-success shadow-lg hover:bg-[#20714c]">
                        Add Device
                    </button>
                </div>
            </div>
        </div>

        <!-- Desktop Layout -->
        <div class="hidden sm:block">
            <div class="flex mb-2">
                <input v-model="searchInput" type="text" placeholder="Search by device name"
                    class="w-full p-2 border border-gray-300 rounded bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <button @click="onSearchClick" :disabled="loading"
                    class="btn text-white btn-success shadow-lg hover:bg-[#20714c] ml-2">
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else>Search</span>
                </button>
                <button @click="onResetClick" class="btn text-white btn-secondary shadow-lg hover:bg-gray-400 ml-2">
                    Reset
                </button>
            </div>
            <button @click="onAddClick" class="btn text-white btn-success shadow-lg hover:bg-[#20714c] mb-2">
                Add Device
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: String,
    loading: Boolean,
});
const emits = defineEmits(['update:modelValue', 'search', 'reset', 'add']);

const searchInput = ref(props.modelValue);

watch(searchInput, (newVal) => {
    emits('update:modelValue', newVal);
});

function onSearchClick() {
    emits('search');
}

function onResetClick() {
    searchInput.value = "";
    emits('update:modelValue', "");
    emits('reset');
}

function onAddClick() {
    emits('add');
}
</script>
