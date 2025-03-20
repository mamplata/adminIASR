<template>
    <div class="card-wrapper m-0 p-0 w-full h-full">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'" :class="[
            'card',
            isThumb
                ? 'border-2 border-black shadow-sm rounded-none'
                : 'border-8 border-black shadow-md rounded-md',
            'flex flex-col relative w-full h-full',
            // Example responsive border and rounded adjustments
            'sm:border-4 sm:rounded-lg'
        ]" :style="{ backgroundImage: `url(${pncBg})`, backgroundSize: 'cover', backgroundPosition: 'center' }">
            <img v-if="!isThumb" :src="pncLogo" alt="PNC Logo" class="absolute top-2 right-2 w-6 h-6 sm:w-8 sm:h-8" />
            <div class="flex flex-col flex-grow m-0 p-0">
                <!-- Header: uses padding for spacing -->
                <div class="card-header flex-none border-b border-black p-4 sm:p-6">
                    <h3 :class="[
                        isThumb
                            ? 'text-sm text-center'
                            : 'whitespace-normal break-words text-center text-3xl md:text-4xl',
                        'font-semibold text-white'
                    ]">
                        {{ announcement.content.title }}
                    </h3>
                </div>
                <!-- Body: fills remaining space with controlled padding and relaxed text -->
                <div class="card-body flex-1 overflow-auto p-4 sm:p-6">
                    <p :class="[
                        isThumb
                            ? 'text-xs'
                            : 'whitespace-normal break-words text-justify text-xl md:text-2xl',
                        'text-white leading-relaxed'
                    ]">
                        {{ announcement.content.body }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card for image announcement -->
        <div v-else-if="announcement.type === 'image'" :class="[
            'card',
            isThumb ? 'shadow-sm rounded-none border-2 border-black' : 'shadow-md rounded-md border-8 border-black',
            'w-full h-full flex justify-center items-center m-0 p-0 relative'
        ]">
            <img :src="`${apiUrl}${announcement.content.file_path}`" :alt="announcement.content.file_name"
                class="w-full h-full object-contain object-center m-0 p-0" />
        </div>
    </div>
</template>

<script setup>
import pncBg from '../../assets/img/pnc-bg.jpg';
import pncLogo from '../../assets/img/pnc-logo-1.png';
const apiUrl = import.meta.env.VITE_API_URL;

const props = defineProps({
    announcement: {
        type: Object,
        required: true,
    },
    isThumb: {
        type: Boolean,
        default: false,
    },
});
</script>

<style scoped>
.card-wrapper,
.card {
    margin: 0;
    padding: 0;
    overflow: hidden;
}
</style>
