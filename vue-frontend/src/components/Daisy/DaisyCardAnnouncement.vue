<template>
    <div class="card-wrapper m-0 p-0 w-full h-full">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'"
            :class="[isThumb ? 'shadow-sm rounded-none border-2 border-black' : 'shadow-md rounded-md border-8 border-black', 'w-full h-full relative']"
            :style="isThumb
                ? { height: thumbnailHeight, backgroundColor: 'black', backgroundImage: `url(${pncBg})`, backgroundSize: 'cover', backgroundPosition: 'center' }
                : { backgroundColor: 'black', backgroundImage: `url(${pncBg})`, backgroundSize: 'cover', backgroundPosition: 'center' }">

            <!-- Main content: Full announcement card -->
            <template v-if="!isThumb">
                <img v-if="!isThumb" :src="pncLogo" alt="PNC Logo"
                    class="absolute top-2 right-2 w-6 h-6 sm:w-8 sm:h-8" />
                <div class="absolute inset-0 p-2 flex flex-col">
                    <h3 class="uppercase font-semibold text-white text-center whitespace-normal break-words"
                        :style="{ fontSize: 'calc(1.2rem + 0.6vh)' }">
                        {{ announcement.content.title }}
                    </h3>
                    <div ref="scrollContainer"
                        class="scroll-container flex-1 items-center relative overflow-hidden mt-2">
                        <div ref="scrollContent" class="scroll-content absolute w-full"
                            :style="{ animation: computedAnimation, '--final-transform': finalTransform }">
                            <p class="text-white whitespace-pre-line break-words text-justify leading-relaxed"
                                :style="{ fontSize: 'calc(0.9rem + 0.8vh)' }">
                                {{ announcement.content.body }}
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Thumbnail: Only show title with smaller font -->
            <template v-else>
                <div class="absolute inset-0 p-2 flex items-center justify-center">
                    <h3 class="uppercase font-semibold text-white text-center"
                        :style="{ fontSize: 'calc(0.8rem + 0.4vh)' }">
                        {{ announcement.content.title }}
                    </h3>
                </div>
            </template>
        </div>

        <!-- Card for image announcement -->
        <div v-else-if="announcement.type === 'image'"
            :class="[isThumb ? 'shadow-sm rounded-none border-2 border-black' : 'shadow-md rounded-md border-8 border-black h-full flex items-center justify-center', 'w-full relative']"
            :style="isThumb ? { height: thumbnailHeight, backgroundColor: 'black' } : { backgroundColor: 'black' }">
            <img :src="`${apiUrl}${announcement.content.file_path}`" :alt="announcement.content.file_name"
                class="block w-full m-0 p-0" :class="isThumb
                    ? 'h-full object-cover object-center'
                    : 'h-96 object-fill object-center'" />
        </div>
    </div>
</template>


<script setup>
import { ref, computed, onMounted, nextTick, onBeforeUnmount, watch } from "vue";
import { useAnnouncementStore } from "@/stores/announcementStore";
import pncBg from "../../assets/img/pnc-bg.jpg";
import pncLogo from "../../assets/img/pnc-logo-1.png";
const apiUrl = import.meta.env.VITE_API_URL;

const announcementStore = useAnnouncementStore();

const props = defineProps({
    announcement: { type: Object, required: true },
    isThumb: { type: Boolean, default: false },
    index: { type: Number, required: true },
    thumbnailHeight: { type: String, default: '6rem' }
});

const scrollContainer = ref(null);
const scrollContent = ref(null);
const needsMarquee = ref(false);

// Use the store's referenceHeight if set; otherwise, use this slide's container height.
function getContainerHeight() {
    // If a reference height is stored, use it; otherwise, measure locally.
    return announcementStore.referenceHeight || (scrollContainer.value ? scrollContainer.value.clientHeight : 0);
}

function checkOverflow() {
    if (props.isThumb) return;
    if (scrollContainer.value && scrollContent.value) {
        const containerHeight = getContainerHeight();
        const contentHeight = scrollContent.value.scrollHeight;
        console.log(`Slide ${props.index}: containerHeight=${containerHeight}, contentHeight=${contentHeight}`);
        needsMarquee.value = contentHeight > containerHeight;
        announcementStore.updateScrollNeeded(props.index, needsMarquee.value);
    }
}

function onAnimationEnd() {
    // Only proceed if this slide is still active
    if (announcementStore.activeIndex !== props.index) return;

    if (needsMarquee.value) {
        setTimeout(() => {
            // Double-check that this slide is still active before advancing.
            if (announcementStore.activeIndex === props.index) {
                announcementStore.handleScrollFinished();
            }
        }, 3000);
    } else {
        if (announcementStore.activeIndex === props.index) {
            announcementStore.handleScrollFinished();
        }
    }
}

// When the active slide changes, wait a little and re-run checkOverflow
watch(
    () => announcementStore.activeIndex,
    (newIndex) => {
        if (props.index === newIndex) {
            setTimeout(() => {
                // Reset animation if needed
                if (scrollContent.value) {
                    scrollContent.value.style.animation = "none";
                    void scrollContent.value.offsetWidth;
                    scrollContent.value.style.animation = computedAnimation.value;
                }
                checkOverflow();
            }, 1000);
        }
    }
);

onMounted(() => {
    nextTick(() => {
        checkOverflow();
        // If this is slide 0 and the store doesn't have a reference yet, store it.
        if (props.index === 0) {
            const containerHeight = scrollContainer.value ? scrollContainer.value.clientHeight : 0;
            if (containerHeight > 0 && !announcementStore.referenceHeight) {
                announcementStore.referenceHeight = containerHeight;
            }
        }
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

const animationDuration = computed(() => {
    if (needsMarquee.value && scrollContainer.value && scrollContent.value) {
        const containerHeight = getContainerHeight();
        const contentHeight = scrollContent.value.scrollHeight;
        const overflowHeight = contentHeight - containerHeight;
        return overflowHeight * 50;
    }
    return 0;
});

const finalTransform = computed(() => {
    if (scrollContent.value && scrollContainer.value && needsMarquee.value) {
        const containerHeight = getContainerHeight();
        const contentHeight = scrollContent.value.scrollHeight;
        const overflowHeight = contentHeight - containerHeight;
        const percent = (overflowHeight / contentHeight) * 100;
        return `translateY(-${percent}%)`;
    }
    return "translateY(0%)";
});

const computedAnimation = computed(() => {
    if (needsMarquee.value && animationDuration.value > 0) {
        return `marquee ${animationDuration.value}ms linear forwards`;
    }
    return "none";
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