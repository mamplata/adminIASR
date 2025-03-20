<template>
    <div>
        <dialog ref="modal" class="modal">
            <div class="modal-box bg-white dark:bg-gray-800 dark:text-white">
                <!-- Modal Title -->
                <h2 class="text-lg font-bold">{{ title }}</h2>

                <!-- Ensure slot content is wrapped properly -->
                <div v-if="$slots.default">
                    <slot></slot>
                </div>
            </div>
        </dialog>
    </div>
</template>

<script setup>
import { ref, defineExpose } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: "Modal",
    },
});

const modal = ref(null);

function showModal() {
    modal.value.showModal();
}

function closeModal() {
    modal.value.close();
}

// Expose methods so the parent component can call them
defineExpose({
    showModal,
    closeModal,
});
</script>

<style scoped></style>
