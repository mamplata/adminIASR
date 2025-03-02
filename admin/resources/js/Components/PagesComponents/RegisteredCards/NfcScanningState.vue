<template>
    <div>
        <div class="py-2 flex flex-col items-center">
            <div class="loading-spinner"></div>
            <p class="mt-2">{{ nfcStatus }}</p>
        </div>
        <div class="modal-action">
            <button class="btn" @click="handleCancel">Cancel</button>
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    nfcStatus: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['cancel-registration'])

function handleCancel() {
    emit('cancel-registration')
}
</script>

<style scoped>
.loading-spinner {
    width: 40px;
    height: 40px;
    border: 5px solid #ccc;
    border-top: 5px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
