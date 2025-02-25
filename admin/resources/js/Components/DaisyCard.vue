<template>
    <dialog ref="modal" class="modal">
        <div class="card modal-box bg-white dark:bg-gray-800 dark:text-white">
            <div class="card-body break-words whitespace-normal">
                <slot>
                    Default Content
                </slot>
            </div>
            <div class="card-actions mt-4 flex justify-end">
                <button @click="closeCard" class="btn btn-secondary text-end">Close</button>
            </div>
        </div>
    </dialog>
</template>

<script setup>
import { ref, onMounted, defineEmits } from 'vue';

const modal = ref(null);
const emit = defineEmits(['close']);

const closeCard = () => {
    modal.value.close();
    emit('close');
};

onMounted(() => {
    modal.value.showModal();
});
</script>

<style scoped>
.modal::backdrop {
    background: rgba(0, 0, 0, 0.5);
}
</style>
