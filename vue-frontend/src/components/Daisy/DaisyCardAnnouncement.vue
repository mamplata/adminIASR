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
                <!-- Scroll container for body text -->
                <div class="scroll-container flex-1 items-center relative overflow-hidden mt-2">
                    <div ref="scrollContent" class="scroll-content absolute w-full"
                        :style="{ animation: computedAnimation }">
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
        </div>

        <!-- Card for image announcement remains unchanged -->
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
import { ref, computed, onMounted, nextTick, onBeforeUnmount } from "vue";
import pncBg from '../../assets/img/pnc-bg.jpg';
import pncLogo from '../../assets/img/pnc-logo-1.png';
const apiUrl = import.meta.env.VITE_API_URL;

const props = defineProps({
    announcement: { type: Object, required: true },
    isThumb: { type: Boolean, default: false },
    active: { type: Boolean, default: false }
});

const scrollContent = ref(null);
// This ref will indicate whether the text overflows its container.
const needsMarquee = ref(false);

// Function to check if the text content overflows its container.
function checkOverflow() {
    if (scrollContent.value) {
        const container = scrollContent.value.parentElement; // the .scroll-container element
        if (container) {
            // If the scrollHeight of the content is greater than the container's height, it overflows.
            needsMarquee.value = scrollContent.value.scrollHeight > container.clientHeight;
        }
    }
}

onMounted(() => {
    // Use nextTick to ensure the DOM is rendered before measuring.
    nextTick(() => {
        checkOverflow();
    });
    window.addEventListener("resize", checkOverflow);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", checkOverflow);
});

// Compute the animation style: only apply marquee if the slide is active and the text overflows.
const computedAnimation = computed(() => {
    return (props.active && needsMarquee.value)
        ? 'marquee 50s linear infinite'
        : 'none';
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

<style>
@keyframes marquee {
    0% {
        transform: translateY(0%);
    }

    100% {
        transform: translateY(-100%);
    }
}

.scroll-content {
    position: relative;
    top: 0;
    width: 100%;
    will-change: transform;
    transform: translateZ(0);
    display: block;
}
</style>
