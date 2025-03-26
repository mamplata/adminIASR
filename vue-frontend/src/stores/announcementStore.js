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
    activeIndex: 0,
    overlayOpacity: 1,
    scrollNeededStatus: {},
    // New property to store the reference container height from the first slide.
    referenceHeight: 0,
  }),
  actions: {
    async fetchAnnouncements() {
      this.loading = true;
      try {
        const response = await HTTP.get("/api/announcements");
        this.announcements = response.data.announcements || [];
        const currentDept = useTimeInStore().selectedDepartment;
        this.filterAnnouncements(currentDept);
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
    setActiveIndex(index) {
      this.activeIndex = index;
      this.referenceHeight = 0; // Reset so the new slide can compute its own height
    },
    nextSlide() {
      console.log(this.scrollNeededStatus);
      if (this.activeIndex < this.filteredAnnouncements.length - 1) {
        this.activeIndex++;
      } else {
        const timeInStore = useTimeInStore();
        if (timeInStore.selectedDepartment !== "GENERAL") {
          timeInStore.selectedDepartment = "GENERAL";
        }
        this.activeIndex = 0;
      }
    },
    handleScrollFinished() {
      this.nextSlide();
    },
    updateScrollNeeded(index, needed) {
      this.scrollNeededStatus[index] = needed;
    },
    // Optional: remove unused functions if not needed.
  },
});
