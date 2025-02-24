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

<script>
export default {
    props: {
        title: { type: String, default: "Modal" },
    },
    methods: {
        showModal() {
            this.$refs.modal.showModal();
        },
        closeModal() {
            this.$refs.modal.close();
        },
    },
};
</script>
a