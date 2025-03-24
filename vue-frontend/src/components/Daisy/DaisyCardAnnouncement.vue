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
                    'uppercase font-semibold text-white text-center',
                    isThumb ? '' : 'whitespace-normal break-words'
                ]" :style="{
                    fontSize: isThumb ? 'calc(1rem + 0.4vh)' : 'calc(1.2rem + 0.6vh)'
                }">
                    {{ announcement.content.title }}
                </h3>
                <!-- Scroll container for body text -->
                <div ref="scrollContainer" class="scroll-container flex-1 items-center relative overflow-hidden mt-2">
                    <div ref="scrollContent" class="scroll-content absolute w-full" :style="{
                        animation: computedAnimation,
                        '--final-transform': finalTransform
                    }">
                        <p :class="[
                            isThumb ? 'text-xs text-white' : 'text-white whitespace-pre-line break-words text-justify leading-relaxed'
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

// We'll need to emit an event to notify the parent when scrolling completes.
const emit = defineEmits(["scrollFinished", "scrollNeeded"]);

const props = defineProps({
    announcement: { type: Object, required: true },
    isThumb: { type: Boolean, default: false },
    active: { type: Boolean, default: false }
});

const scrollContainer = ref(null);
const scrollContent = ref(null);
const needsMarquee = ref(false);

// Check whether the text overflows its container.
function checkOverflow() {
    if (scrollContainer.value && scrollContent.value) {
        needsMarquee.value =
            scrollContent.value.scrollHeight > scrollContainer.value.clientHeight;
    }
}

onMounted(() => {
    nextTick(() => {
        checkOverflow();
        // Emit the status after measuring.
        emit("scrollNeeded", needsMarquee.value);
    });
    window.addEventListener("resize", checkOverflow);
    if (scrollContent.value) {
        scrollContent.value.addEventListener("animationend", onAnimationEnd);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", checkOverflow);
    if (scrollContent.value) {
        scrollContent.value.removeEventListener("animationend", onAnimationEnd);
    }
});

// Compute the animation duration based on the amount of overflow.
// Adjust the multiplier (here, 20 ms per pixel) to control speed.
const animationDuration = computed(() => {
    if (props.active && needsMarquee.value && scrollContainer.value && scrollContent.value) {
        const containerHeight = scrollContainer.value.clientHeight;
        const contentHeight = scrollContent.value.scrollHeight;
        const overflowHeight = contentHeight - containerHeight;
        return overflowHeight * 50;
    }
    return 0;
});

const finalTransform = computed(() => {
    if (scrollContent.value && scrollContainer.value && needsMarquee.value) {
        const containerHeight = scrollContainer.value.clientHeight;
        const contentHeight = scrollContent.value.scrollHeight;
        const overflowHeight = contentHeight - containerHeight;
        // Calculate the percentage of the total height this overflow represents.
        const percent = (overflowHeight / contentHeight) * 100;
        return `translateY(-${percent}%)`;
    }
    return 'translateY(0%)';
});


// Compute the CSS animation style.
// If the slide is active and the text overflows, run a single iteration (no "infinite").
// Otherwise, no animation.
const computedAnimation = computed(() => {
    if (props.active && needsMarquee.value && animationDuration.value > 0) {
        return `marquee ${animationDuration.value}ms linear forwards`;
    }
    return 'none';
});

// Listen for the animation end event and notify the parent.
function onAnimationEnd() {
    if (needsMarquee.value) {
        // For long text slides, wait 3 seconds before emitting.
        setTimeout(() => {
            emit("scrollFinished");
        }, 3000);
    } else {
        // For short text slides, emit immediately.
        emit("scrollFinished");
    }
}
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
        transform: var(--final-transform, translateY(0%));
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