<template>
    <dialog ref="modal" class="modal">
        <div class="modal-box bg-white dark:bg-gray-800 dark:text-white">
            <!-- Card for text announcement -->
            <div v-if="announcement.type === 'text'" class="card shadow-md mb-4 rounded-md"
                :style="{ backgroundImage: 'url(' + pncBg + ')', backgroundSize: 'cover', position: 'relative' }">
                <!-- Logo in the top-right corner -->
                <img :src="pncLogo" alt="PNC Logo" class="absolute top-2 right-2 w-6 h-6" />

                <!-- Flex container with overflow handling -->
                <div class="d-flex justify-center align-center"
                    style="height: auto; overflow: hidden; flex-direction: column;">
                    <div class="card-header px-4 py-2 border-b border-gray-300 dark:border-gray-600 text-wrap">
                        <h4 class="card-title font-semibold text-gray-800 dark:text-white">
                            {{ announcement.content.title }}
                        </h4>
                    </div>
                    <div class="card-body px-4 py-2 text-wrap">
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ announcement.content.body }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card for image announcement -->
            <div v-else-if="announcement.type === 'image'" class="card shadow-md mb-4">
                <img :src="announcement.content.file_path" :alt="announcement.content.file_name"
                    class="rounded-md w-full object-cover" />
            </div>

            <!-- Fallback card -->
            <div v-else class="card shadow-md mb-4">
                <div class="card-body px-4 py-2">
                    <p class="text-gray-600 dark:text-gray-400">
                        No preview available for this announcement.
                    </p>
                </div>
            </div>

            <div class="modal-action">
                <button @click="closeCard" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </dialog>
</template>

<script>
import pncBg from '../../../resources/assets/img/pnc-bg.jpg';
import pncLogo from '../../assets/img/pnc-logo.png';

export default {
    data() {
        return {
            pncBg,
            pncLogo,
        }
    },
    props: {
        announcement: {
            type: Object,
            required: true
        }
    },
    mounted() {
        // Automatically show the modal when the component mounts
        this.$refs.modal.showModal();
    },
    methods: {
        closeCard() {
            this.$refs.modal.close();
            this.$emit('close');
        }
    }
};
</script>

<style scoped>
.modal::backdrop {
    background: rgba(0, 0, 0, 0.5);
}

/* Flex container settings */
.d-flex {
    display: flex;
    flex-direction: column;
    /* Stack content vertically */
    height: auto;
    /* Ensure the container grows with the content */
    overflow: hidden;
    /* Prevent overflow outside the container */
}

.justify-center {
    justify-content: center;
    /* Center content vertically */
}

.align-center {
    align-items: center;
    /* Center content horizontally */
}

.card-body {
    height: auto;
    /* Let the body grow based on content */
    padding: 1rem;
    /* Optionally adjust padding if needed */
}

.text-wrap {
    white-space: normal;
    /* Allow text to wrap normally */
    overflow-wrap: break-word;
    /* Break long words if needed */
    word-wrap: break-word;
    /* Fallback for older browsers */
}
</style>
