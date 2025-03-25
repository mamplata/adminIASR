// src/stores/announcementStore.js
import { defineStore } from "pinia";
import HTTP from "@/http";
import { useTimeInStore } from "@/stores/timeInStore";

export const useAnnouncementStore = defineStore("announcement", {
  state: () => ({
    announcements: [],
    filteredAnnouncements: [],
    loading: true,
    error: null,
    mainSwiper: null,
    thumbsSwiper: null,
    activeIndex: 0,
    overlayOpacity: 1,
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
      console.log("Selected department:", selectedDepartment);
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
          const timeInStore = useTimeInStore();
          if (timeInStore.selectedDepartment !== "GENERAL") {
            // Reset to GENERAL if the selected department is not GENERAL
            timeInStore.selectedDepartment = "GENERAL";
          }
          this.mainSwiper.slideNext();
        }, 3000);
      }
    },
    setThumbsSwiper(swiper) {
      this.thumbsSwiper = swiper;
    },
    onSlideChangeTransitionStart(swiper) {
      this.overlayOpacity = 1;
      const currentAnnouncement = this.filteredAnnouncements[this.activeIndex];
      const needsScroll = this.scrollNeededStatus[this.activeIndex];
      if (
        currentAnnouncement &&
        currentAnnouncement.type === "text" &&
        needsScroll
      ) {
        swiper.autoplay.stop();
      }
    },
    onSlideChangeTransitionEnd(swiper) {
      this.overlayOpacity = 0;
      this.activeIndex = swiper.realIndex;
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
            // Check if this is the last slide
            if (this.activeIndex === this.filteredAnnouncements.length - 1) {
              const timeInStore = useTimeInStore();
              if (timeInStore.selectedDepartment !== "GENERAL") {
                // Reset to GENERAL if the selected department is not GENERAL
                timeInStore.selectedDepartment = "GENERAL";
              }
              this.mainSwiper.slideNext();
            }
          }
        }, 3000); // adjust delay as needed
      }
    },

    resetDepartment() {
      // Reset the department to GENERAL by filtering announcements accordingly.
      this.filteredAnnouncements = this.announcements.filter(
        (announcement) => announcement.departments.trim() === "GENERAL"
      );
    },
    handleScrollFinished() {
      if (this.mainSwiper && typeof this.mainSwiper.slideNext === "function") {
        this.mainSwiper.slideNext();
      }
    },
    updateScrollNeeded(index, needed) {
      this.scrollNeededStatus[index] = needed;
    },
  },
});
