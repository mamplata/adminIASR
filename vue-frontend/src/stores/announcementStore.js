// src/stores/announcementStore.js
import { defineStore } from "pinia";
import HTTP from "@/http";

export const useAnnouncementStore = defineStore("announcement", {
  state: () => ({
    announcements: [],
    filteredAnnouncements: [],
    loading: true,
    error: null,
    // New state for swiper handling
    mainSwiper: null,
    thumbsSwiper: null,
    activeIndex: 0,
    overlayOpacity: 1,
    // Object to store for each slide whether scrolling is needed
    scrollNeededStatus: {},
  }),
  actions: {
    async fetchAnnouncements() {
      this.loading = true;
      try {
        const response = await HTTP.get("/api/announcements");
        this.announcements = response.data.announcements || [];
        // Default filter to GENERAL
        this.filteredAnnouncements = this.announcements.filter(
          (announcement) => announcement.departments.trim() === "GENERAL"
        );
      } catch (err) {
        this.error = err;
        console.error("Error fetching announcements:", err);
      } finally {
        this.loading = false;
      }
    },
    filterAnnouncements(selectedDepartment) {
      if (selectedDepartment.trim() === "GENERAL") {
        this.filteredAnnouncements = this.announcements.filter(
          (announcement) => announcement.departments.trim() === "GENERAL"
        );
        return;
      }
      const newFiltered = this.announcements.filter((announcement) => {
        const announcementDepartments = announcement.departments.trim();
        // Split groups by semicolon.
        const groups = announcementDepartments
          .split(";")
          .map((group) => group.trim())
          .filter(Boolean);
        const selectedParts = selectedDepartment
          .split(":")
          .map((s) => s.trim());
        if (selectedParts.length !== 2) return false;
        const [selectedDept, selectedProgram] = selectedParts;
        return groups.some((group) => {
          if (group.includes(":")) {
            const [dept, programsStr] = group.split(":");
            const programs = programsStr.split(",").map((p) => p.trim());
            return (
              dept.trim() === selectedDept && programs.includes(selectedProgram)
            );
          }
          return group === selectedDepartment;
        });
      });
      this.filteredAnnouncements = newFiltered;
    },
    // Swiper-related functions:
    setMainSwiper(swiper) {
      this.mainSwiper = swiper;
      // Delay check to allow scroll status to update
      setTimeout(() => {
        if (this.filteredAnnouncements.length > 0) {
          const firstAnnouncement = this.filteredAnnouncements[0];
          const needsScroll = this.scrollNeededStatus[0];
          if (
            firstAnnouncement &&
            firstAnnouncement.type === "text" &&
            needsScroll
          ) {
            swiper.autoplay.stop();
          }
        }
      }, 500);
      if (this.filteredAnnouncements.length === 1) {
        setTimeout(() => {
          swiper.autoplay.stop();
          swiper.slideToLoop(0, 300);
          swiper.autoplay.start();
        }, 3000);
      }
    },
    setThumbsSwiper(swiper) {
      this.thumbsSwiper = swiper;
    },
    onSlideChangeTransitionStart() {
      this.overlayOpacity = 1;
    },
    onSlideChangeTransitionEnd(swiper) {
      this.overlayOpacity = 0;
      this.activeIndex = swiper.activeIndex;
      const currentAnnouncement = this.filteredAnnouncements[this.activeIndex];
      const needsScroll = this.scrollNeededStatus[this.activeIndex];

      if (
        currentAnnouncement &&
        currentAnnouncement.type === "text" &&
        needsScroll
      ) {
        // For overflowing slides, we assume onAnimationEnd (from DaisyCardAnnouncement)
        // will trigger handleScrollFinished() to move to the next slide.
      } else {
        // For non-overflowing slides, set a fallback timeout to move to the next slide.
        setTimeout(() => {
          if (
            this.mainSwiper &&
            typeof this.mainSwiper.slideNext === "function"
          ) {
            this.mainSwiper.slideNext();
          }
        }, 3000); // adjust delay as needed
      }

      // Check if at the last slide and reset department.
      if (
        this.filteredAnnouncements.length > 1 &&
        swiper.activeIndex === this.filteredAnnouncements.length - 1
      ) {
        setTimeout(() => {
          swiper.autoplay.stop();
          swiper.slideToLoop(0, 300);
          swiper.autoplay.start();
          this.resetDepartment();
        }, 1000);
      }
    },

    resetDepartment() {
      // Reset the department to GENERAL by filtering announcements accordingly.
      this.filteredAnnouncements = this.announcements.filter(
        (announcement) => announcement.departments.trim() === "GENERAL"
      );
    },
    handleScrollFinished() {
      const currentIndex = this.activeIndex;
      const needsScroll = this.scrollNeededStatus[currentIndex];
      if (needsScroll) {
        // If the slide still needs to scroll, wait a bit more before proceeding.
        setTimeout(() => {
          this.handleScrollFinished();
        }, 500); // adjust delay as needed
      } else if (
        this.mainSwiper &&
        typeof this.mainSwiper.slideNext === "function"
      ) {
        this.mainSwiper.slideNext();
      }
    },
    updateScrollNeeded(index, needed) {
      this.scrollNeededStatus[index] = needed;
    },
  },
});
