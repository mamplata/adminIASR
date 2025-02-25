<template>
    <select v-model="internalValue"
        class="input w-full input-bordered bg-white text-gray-900 border-gray-500 dark:bg-gray-800 dark:text-white dark:border-gray-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:focus:border-blue-400 dark:focus:ring-blue-500">
        <option value="" disabled>{{ placeholder }}</option>
        <option v-for="option in options" :key="optionKey(option)" :value="optionValue(option)">
            {{ optionLabel(option) }}
        </option>
    </select>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({
    modelValue: [String, Number],
    placeholder: {
        type: String,
        default: 'Select an option'
    },
    options: {
        type: Array,
        default: () => []
    }
});
const emits = defineEmits(["update:modelValue"]);

const internalValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emits("update:modelValue", val);
    }
});

// Helper functions to handle options as strings or objects
function optionKey(option) {
    return typeof option === 'object' ? option.id || option.value || option.label : option;
}
function optionValue(option) {
    return typeof option === 'object' ? option.id || option.value : option;
}
function optionLabel(option) {
    return typeof option === 'object' ? option.name || option.label : option;
}
</script>
