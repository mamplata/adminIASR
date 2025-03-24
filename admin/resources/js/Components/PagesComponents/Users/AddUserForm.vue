<template>
    <form @submit.prevent="handleSubmit" @keydown.enter="handleEnter">
        <!-- Name Field -->
        <label class="label text-gray-700 dark:text-gray-300">Name</label>
        <input v-model="form.name" type="text" :class="[
            'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
            form.errors.name ? 'border-red-500' : 'border-gray-500'
        ]" required />
        <div v-if="form.errors.name" class="text-sm mb-2 text-red-500 dark:text-white">{{ form.errors.name }}</div>

        <!-- Email Field -->
        <label class="label text-gray-700 dark:text-gray-300">Email</label>
        <input v-model="form.email" type="email" :class="[
            'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
            form.errors.email ? 'border-red-500' : 'border-gray-500'
        ]" required />
        <div v-if="form.errors.email" class="text-sm mb-2 text-red-500 dark:text-white">{{ form.errors.email }}</div>

        <!-- Modal Actions -->
        <div class="modal-action mt-4">
            <button type="button" @click="$emit('cancel')" class="mr-4 hover:underline text-black dark:text-white">
                Cancel
            </button>
            <button type="submit"
                class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                <span v-else>Save</span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    form: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['submit', 'cancel']);

// Handle the form submission by emitting a submit event.
function handleSubmit() {
    emit('submit');
}

// Handle Enter key press globally within the form
function handleEnter(event) {
    if (event.target.tagName !== 'TEXTAREA') {
        event.preventDefault(); // Prevent unintended line breaks
        handleSubmit();
    }
}
</script>
