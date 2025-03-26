<template>
    <dialog ref="modal" class="modal">
        <!-- Modal box container -->
        <div class="modal-box bg-white dark:bg-gray-800 dark:text-white">

            <!-- 1) Text Announcement -->
            <div v-if="announcement.type === 'text'" class="relative card">
                <!-- The background image is now a regular <img> -->
                <img :src="pncBg" class="w-full object-contain" alt="Announcement Template" />

                <!-- Overlaid text content (absolute positioning) -->
                <div class="absolute top-0 left-0 w-full p-4">
                    <div class="card-header">
                        <h3
                            class="uppercase font-semibold text-white whitespace-normal break-words text-center text-2xl title">
                            {{ announcement.content.title }}
                        </h3>
                    </div>
                    <div class="card-body mt-4">
                        <p class="text-black font-black text-xl whitespace-pre-line break-words text-justify px-8 ">
                            {{ announcement.content.body }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2) Image Announcement -->
            <div v-else-if="announcement.type === 'image'" class="card shadow-md mb-4">
                <img :src="announcement.content.file_path" :alt="announcement.content.file_name"
                    class="rounded-md w-full object-cover" />
            </div>

            <!-- 3) Fallback Card -->
            <div v-else class="card shadow-md mb-4">
                <div class="card-body px-4 py-2">
                    <p class="text-gray-600 dark:text-gray-400">
                        No preview available for this announcement.
                    </p>
                </div>
            </div>

            <!-- Modal action with button right-aligned -->
            <div class="modal-action flex justify-end">
                <button @click="closeCard" class="btn btn-soft mt-6">Close</button>
            </div>
        </div>
    </dialog>
</template>

<script>
import pncBg from '../../../assets/img/pncAnnouncement.png';
import pncLogo from '../../../assets/img/pnc-logo.png';

export default {
    name: 'AnnouncementModal',
    props: {
        announcement: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            pncBg,
            pncLogo,
        };
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
.title {
    margin-top: 100px;
}

/* Dark backdrop when modal is open */
.modal::backdrop {
    background: rgba(0, 0, 0, 0.5);
}

/* Let the modal expand up to 80% of the viewport height, scrolling if needed */
.modal-box {
    max-width: 40rem !important;
    max-height: 80vh;
    overflow-y: auto;
}

/* Positioning for the card, if needed */
.card {
    position: relative;
}

.card-body {
    max-height: 115px;
    overflow-y: auto;
    padding: 0 !important;
}

/* Ensure text wraps properly */
.text-wrap {
    white-space: normal;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

/* Modal action alignment */
.modal-action {
    margin: 0;
}
</style>
