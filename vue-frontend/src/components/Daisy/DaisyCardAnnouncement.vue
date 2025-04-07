<template>
    <div class="card-wrapper m-0 p-0">
        <!-- Card for text announcement -->
        <div v-if="announcement.type === 'text'"
            :class="[isThumb ? 'shadow-sm rounded-none border-2 border-black' : 'shadow-md rounded-md', 'w-full h-full relative']"
            :style="isThumb
                ? { height: thumbnailHeight, backgroundColor: 'black' }
                : { backgroundColor: 'black' }">
            <!-- Background image for text announcement -->
            <img :src="pncBg" alt="Background" class="absolute inset-0 w-full h-full object-contain" />

            <!-- Main content: Full announcement card -->
            <template v-if="!isThumb">
                <div class="absolute inset-0 flex flex-col">
                    <!-- Responsive Title -->
                    <h3 class="text-container uppercase font-semibold text-white text-center whitespace-normal break-words"
                        :style="{ fontSize: 'calc(1dvw + 1.3dvh)', zIndex: 10 }">
                        {{ truncatedTitle }}
                    </h3>
                    <!-- Responsive Text Container -->
                    <div ref="scrollContainer" class="scroll-container flex-1 items-center overflow-hidden mt-[35dvh]">
                        <div ref="scrollContent" class="scroll-content absolute w-full"
                            :style="{ animation: computedAnimation, '--final-transform': finalTransform }">
                            <p class="text-black font-semibold whitespace-pre-line break-words text-justify leading-relaxed px-[6dvw]"
                                :style="{ fontSize: 'calc(1.2vw + 1.2vh)' }">
                                {{ announcement.content.body }}
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </div>


        <!-- Card for image announcement -->
        <div v-else-if="announcement.type === 'image'"
            :class="[isThumb ? 'shadow-sm rounded-none border-2 border-black' : 'shadow-md rounded-md h-full flex items-center justify-center', 'w-full relative']"
            :style="isThumb ? { height: thumbnailHeight, backgroundColor: 'black' } : { backgroundColor: 'black' }">
            <img :src="`${apiUrl}${announcement.content.file_path}`" :alt="announcement.content.file_name"
                class="block m-0 p-0 object-contain object-center" :class="isThumb ? 'w-full h-full' : ''"
                :style="!isThumb ? { maxHeight: '65vh', width: '100vw', height: '100vh - 20vh' } : {}" />
        </div>
    </div>
</template>


<script setup>
import { ref, computed, onMounted, nextTick, onBeforeUnmount, watch } from "vue";
import { useAnnouncementStore } from "@/stores/announcementStore";
import pncBg from "../../assets/img/pncAnnouncement.png";
const apiUrl = import.meta.env.VITE_API_URL;

const announcementStore = useAnnouncementStore();

const props = defineProps({
    announcement: { type: Object, required: true },
    isThumb: { type: Boolean, default: false },
    index: { type: Number, required: true },
    thumbnailHeight: { type: String, default: 'calc(5vw + 5vh)' }
});

const scrollContainer = ref(null);
const scrollContent = ref(null);
const needsMarquee = ref(false);

// Use the store's referenceHeight if set; otherwise, use this slide's container height.
function getContainerHeight() {
    // If a reference height is stored, use it; otherwise, measure locally.
    return announcementStore.referenceHeight || (scrollContainer.value ? scrollContainer.value.clientHeight : 0);
}

/**
 * Checks if the content of the slide is too tall to fit in the container,
 * and if so, enables the marquee animation. Stores the result in the
 * announcementStore so that it can be retrieved later.
 */
function checkOverflow() {
    if (props.isThumb) return;
    if (scrollContainer.value && scrollContent.value) {
        const containerHeight = getContainerHeight();
        const contentHeight = scrollContent.value.scrollHeight;
        needsMarquee.value = contentHeight > containerHeight;
        announcementStore.updateScrollNeeded(props.index, needsMarquee.value);
    }
}

/**
 * Handles the end of the marquee animation, advancing to the next slide.
 *
 * This function is called when the marquee animation ends, and it checks
 * whether the animation was needed in the first place. If it was, it waits
 * 3 seconds and then calls handleScrollFinished on the announcement store.
 * If the animation wasn't needed, it calls handleScrollFinished immediately.
 * In both cases, it double-checks that this slide is still active before
 * advancing.
 */
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
    // Wait for the component to be fully mounted before checking for overflow
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
    // Set up event listeners
    window.addEventListener("resize", checkOverflow);
    if (scrollContent.value) {
        scrollContent.value.addEventListener("animationend", onAnimationEnd);
    }
});

// Clean up event listeners when the component is unmounted
onBeforeUnmount(() => {
    window.removeEventListener("resize", checkOverflow);
    if (scrollContent.value) {
        scrollContent.value.removeEventListener("animationend", onAnimationEnd);
    }
});

// Compute animation duration based on overflow height
const animationDuration = computed(() => {
    if (needsMarquee.value && scrollContainer.value && scrollContent.value) {
        const containerHeight = getContainerHeight();
        const contentHeight = scrollContent.value.scrollHeight + 165;
        const overflowHeight = contentHeight - containerHeight;
        return overflowHeight * 50;
    }
    return 0;
});

const finalTransform = computed(() => {
    if (scrollContent.value && scrollContainer.value && needsMarquee.value) {
        const containerHeight = getContainerHeight();
        const contentHeight = scrollContent.value.scrollHeight + 165;
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

const truncatedTitle = computed(() => {
    const title = props.announcement.content.title || "";
    return title.length > 41 ? title.substring(0, 38) + "..." : title;
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

.text-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin-top: -8.5dvw;
}
</style>