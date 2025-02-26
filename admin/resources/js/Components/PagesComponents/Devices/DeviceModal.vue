<template>
    <DaisyModal ref="modalRef" :title="isEditMode ? 'Edit Device' : 'Add Device'">
        <template #default>
            <form @submit.prevent="onSubmit">
                <!-- Name -->
                <label class="label text-gray-700 dark:text-gray-300">Name</label>
                <input v-model="localForm.name" type="text"
                    class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                    required />

                <!-- Machine ID -->
                <label class="label text-gray-700 dark:text-gray-300">Machine ID</label>
                <input v-model="localForm.machineId" type="text"
                    class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                    required />

                <!-- Hardware UID -->
                <label class="label text-gray-700 dark:text-gray-300">Hardware UID</label>
                <input v-model="localForm.hardwareUID" type="text"
                    class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                    required />

                <!-- MAC Address -->
                <label class="label text-gray-700 dark:text-gray-300">MAC Address</label>
                <input v-model="localForm.MACAdress" type="text"
                    class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                    required />

                <!-- Device Fingerprint -->
                <label class="label text-gray-700 dark:text-gray-300">Device Fingerprint</label>
                <input v-model="localForm.deviceFingerprint" type="text"
                    class="input input-bordered border border-gray-500 rounded bg-white text-gray-900 w-full mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-300 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-500"
                    required />

                <!-- Modal Actions -->
                <div class="modal-action">
                    <button type="button" @click="onCancel"
                        class="btn btn-secondary text-white dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit"
                        class="btn btn-success text-white hover:bg-[#20714c] dark:bg-green-700 dark:hover:bg-green-600">
                        <span v-if="processing" class="loading loading-spinner loading-sm"></span>
                        <span v-else>{{ isEditMode ? 'Update' : 'Save' }}</span>
                    </button>
                </div>
            </form>
        </template>
    </DaisyModal>
</template>

<script setup>
import { ref, computed } from 'vue';
import DaisyModal from '@/Components/DaisyModal.vue';

const props = defineProps({
    modelValue: Object, // initial form data
    isEditMode: Boolean,
    processing: Boolean,
});
const emits = defineEmits(['update:modelValue', 'submit', 'cancel']);

const modalRef = ref(null);

// Create a computed binding to the parent's deviceForm
const localForm = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emits('update:modelValue', value);
    }
});

function onSubmit() {
    emits('submit', localForm);
}

function onCancel() {
    modalRef.value.closeModal();
    emits('cancel');
}

// Optionally expose methods to show/close the modal
function showModal() {
    modalRef.value.showModal();
}
function closeModal() {
    modalRef.value.closeModal();
}

defineExpose({ showModal, closeModal });
</script>
