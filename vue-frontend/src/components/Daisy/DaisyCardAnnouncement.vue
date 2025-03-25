<template>
    <div class="card-wrapper m-0 p-0 w-full h-full">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'" :class="[
            'card',
            isThumb
                ? 'shadow-sm rounded-none border-2 border-black'
                : 'shadow-md rounded-md border-8 border-black',
            'w-full h-full flex justify-center items-center m-0 p-0 relative'
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
                    'uppercase font-semibold text-black text-center',
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
                            isThumb ? 'text-xs text-black' : 'text-black whitespace-pre-line break-words text-justify leading-relaxed'
                        ]" :style="{ fontSize: isThumb ? 'calc(0.6rem + 0.4vh)' : 'calc(0.9rem + 0.8vh)' }">
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
import { useAnnouncementStore } from "@/stores/announcementStore";
import pncBg from "../../assets/img/pncAnnouncement.png";
import pncLogo from "../../assets/img/pnc-logo-1.png";
const apiUrl = import.meta.env.VITE_API_URL;

const announcementStore = useAnnouncementStore();

// Accept props including an index for this slide; removed the active prop.
const props = defineProps({
    announcement: { type: Object, required: true },
    isThumb: { type: Boolean, default: false },
    index: { type: Number, required: true }
});

const scrollContainer = ref(null);
const scrollContent = ref(null);
const needsMarquee = ref(false);

// Determine if this slide is active based on its index.
const isActive = computed(() => props.index === announcementStore.activeIndex);

// Check whether the text overflows its container.
function checkOverflow() {
    // Skip marquee calculation for thumbnails
    if (props.isThumb) return;
    if (scrollContainer.value && scrollContent.value) {
        needsMarquee.value =
            scrollContent.value.scrollHeight > scrollContainer.value.clientHeight;
        announcementStore.updateScrollNeeded(props.index, needsMarquee.value);
    }
}

onMounted(() => {
    nextTick(() => {
        checkOverflow();
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
const animationDuration = computed(() => {
    if (
        isActive.value &&
        needsMarquee.value &&
        scrollContainer.value &&
        scrollContent.value
    ) {
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
        const percent = (overflowHeight / contentHeight) * 100;
        return `translateY(-${percent}%)`;
    }
    return "translateY(0%)";
});

const computedAnimation = computed(() => {
    if (isActive.value && needsMarquee.value && animationDuration.value > 0) {
        return `marquee ${animationDuration.value}ms linear forwards`;
    }
    return "none";
});

function onAnimationEnd() {
    if (needsMarquee.value) {
        setTimeout(() => {
            announcementStore.handleScrollFinished();
        }, 3000);
    } else {
        announcementStore.handleScrollFinished();
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