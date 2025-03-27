// src/stores/scannerPortStore.js
import { defineStore } from "pinia";
import { getSocket } from "@/composables/socket";

export const useScannerPortStore = defineStore("scannerPort", {
  state: () => ({
    timeInInfo: null,
    timeOutInfo: null,
    newScannerInfo: null,
    socket: null,
    isPortStatusModalOpen: false, // modal state
  }),
  actions: {
    initializeSocket() {
      this.socket = getSocket();
      if (!this.socket) {
        console.error("Socket connection not initialized.");
        return;
      }
      this.setupSocketListeners();
    },
    setupSocketListeners() {
      if (!this.socket) return;

      this.socket.on("scannerDetected", (data) => {
        // If the scanner is not assigned, check if it was previously assigned and clear it
        if (!data.assigned) {
          if (this.timeInInfo && this.timeInInfo.uniqueKey === data.uniqueKey) {
            this.timeInInfo = null;
          }
          if (
            this.timeOutInfo &&
            this.timeOutInfo.uniqueKey === data.uniqueKey
          ) {
            this.timeOutInfo = null;
          }
          this.newScannerInfo = data;
          return;
        }

        // For assigned scanners, update the corresponding store value
        if (data.role === "Time In") {
          this.timeInInfo = data;
        } else if (data.role === "Time Out") {
          this.timeOutInfo = data;
        }
      });

      this.socket.on("scannerAssigned", (data) => {
        if (data.role === "Time In") {
          this.timeInInfo = { ...data, online: true, role: "Time In" };
        } else if (data.role === "Time Out") {
          this.timeOutInfo = { ...data, online: true, role: "Time Out" };
        }
        this.newScannerInfo = null;
      });

      this.socket.on("scannerDisconnected", (data) => {
        if (
          this.newScannerInfo &&
          this.newScannerInfo.uniqueKey === data.uniqueKey
        ) {
          this.newScannerInfo = null;
        }
        if (this.timeInInfo && this.timeInInfo.uniqueKey === data.uniqueKey) {
          this.timeInInfo = null;
        }
        if (this.timeOutInfo && this.timeOutInfo.uniqueKey === data.uniqueKey) {
          this.timeOutInfo = null;
        }
      });
    },
    assignRole(uniqueKey, role) {
      if (this.socket) {
        this.socket.emit("assignRole", { uniqueKey, role });
      }
    },
    openPortStatusModal() {
      this.isPortStatusModalOpen = true;
    },
    closePortStatusModal() {
      this.isPortStatusModalOpen = false;
    },
  },
});
