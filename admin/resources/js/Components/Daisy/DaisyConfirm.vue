<template>
    <div v-if="visible" class="modal modal-open" ref="modalContainer">
        <div class="modal-box relative bg-white dark:bg-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ title }}
            </h3>
            <p class="py-4 text-gray-700 dark:text-gray-300">
                {{ message }}
            </p>
            <div class="modal-action">
                <button class="btn text-white btn-success shadow-lg hover:bg-[#20714c]" @click="handleConfirm">
                    Confirm
                </button>
                <button class="btn btn-ghost" @click="handleCancel">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: "Confirm",
        },
        message: {
            type: String,
            default: "Are you sure you want to proceed?",
        },
    },
    methods: {
        handleConfirm() {
            this.$emit("confirm");
        },
        handleCancel() {
            this.$emit("cancel");
        },
        onGlobalKeyDown(e) {
            if (this.visible && e.key === "Enter") {
                e.preventDefault();
                this.handleConfirm();
            }
        }
    },
    mounted() {
        document.addEventListener("keydown", this.onGlobalKeyDown);
    },
    beforeUnmount() {
        document.removeEventListener("keydown", this.onGlobalKeyDown);
    },
};
</script>

<style scoped>
.modal-box {
    max-width: 32rem !important;
}
</style>
