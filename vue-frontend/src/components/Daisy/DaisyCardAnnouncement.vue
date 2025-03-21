<template>
    <div class="card-wrapper m-0 p-0 w-full h-full">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'" :class="[
            'card',
            isThumb
                ? 'border-2 border-black shadow-sm rounded-none'
                : 'border-8 border-black shadow-md rounded-md',
            'relative w-full h-full',
            'sm:border-4 sm:rounded-lg'
        ]" :style="{
            backgroundImage: `url(${pncBg})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center'
        }">
            <img v-if="!isThumb" :src="pncLogo" alt="PNC Logo" class="absolute top-2 right-2 w-6 h-6 sm:w-8 sm:h-8" />
            <!-- Absolute overlay container for text -->
            <div class="absolute inset-0 p-2 flex flex-col">
                <!-- Title at the top -->
                <h3 :class="[
                    'font-semibold text-white text-center',
                    isThumb ? '' : 'whitespace-normal break-words'
                ]" :style="{
                    fontSize: isThumb ? 'calc(1rem + 0.4vh)' : 'calc(1.2rem + 0.6vh)'
                }">
                    {{ announcement.content.title }}
                </h3>

                <!-- Body content centered below the title -->
                <div class="flex flex-grow items-center justify-center">
                    <p :class="[
                        isThumb ? 'text-xs' : 'whitespace-normal break-words text-center',
                        'text-white leading-relaxed'
                    ]" :style="{
                        fontSize: isThumb ? 'calc(0.6rem + 0.4vh)' : 'calc(0.9rem + 0.8vh)'
                    }">
                        {{ announcement.content.body }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Card for image announcement -->
        <div v-else-if="announcement.type === 'image'" :class="[
            'card',
            isThumb
                ? 'shadow-sm rounded-none border-2 border-black'
                : 'shadow-md rounded-md border-8 border-black',
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
