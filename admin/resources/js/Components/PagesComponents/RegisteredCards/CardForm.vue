<template>
  <form @submit.prevent="handleSubmit">
    <!-- Student ID Field -->
    <label class="label text-gray-700 dark:text-gray-300">Student ID</label>
    <input v-model="form.studentId" type="text" :class="[
      'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
      form.errors.studentId ? 'border-red-500' : 'border-gray-500'
    ]" required />
    <div v-if="form.errors.studentId" class="text-sm mb-2 text-red-500 dark:text-white">
      {{ form.errors.studentId }}
    </div>

    <!-- NFC UID Field -->
    <label class="label text-gray-700 dark:text-gray-300">NFC UID</label>
    <input v-model="form.uid" type="text" :class="[
      'input input-bordered rounded bg-white text-gray-900 w-full mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-500',
      form.errors.uid ? 'border-red-500' : 'border-gray-500'
    ]" required />
    <div v-if="form.errors.uid" class="text-sm mb-2 text-red-500 dark:text-white">
      {{ form.errors.uid }}
    </div>

    <!-- Modal Actions -->
    <div class="modal-action mt-4">
      <button type="button" @click="$emit('cancel')"
        class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
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
    required: true,
  }
});

const emit = defineEmits(['submit', 'cancel']);

function handleSubmit() {
  emit('submit');
}
</script>
