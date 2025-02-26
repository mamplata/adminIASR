<template>
    <dialog ref="modal" class="modal">
        <div class="modal-box bg-white dark:bg-gray-800 dark:text-white">
            <!-- Card for text announcement -->
            <div v-if="announcement.type === 'text'" class="card shadow-md mb-4 rounded-md"
                :style="{ backgroundImage: 'url(' + pncBg + ')', backgroundSize: 'cover', position: 'relative' }">
                <!-- Logo in the top-right corner -->
                <img :src="pncLogo" alt="PNC Logo" class="absolute top-2 right-2 w-6 h-6" />

                <!-- Flex container with overflow handling -->
                <div class="">
                    <div class="m-5 card-header px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                        <h3
                            class="font-semibold text-gray-800 dark:text-white whitespace-normal break-words text-center">
                            {{ announcement.content.title }}
                        </h3>
                    </div>
                    <div class="card-body m-5 px-4 py-2">
                        <p class="text-gray-600 dark:text-gray-400 whitespace-normal break-words text-justify">
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

            <!-- Modal action with button right-aligned -->
            <div class="modal-action flex justify-end">
                <button @click="closeCard" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </dialog>

</template>

<script>
import pncBg from '../../assets/img/pnc-bg.jpg';
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
