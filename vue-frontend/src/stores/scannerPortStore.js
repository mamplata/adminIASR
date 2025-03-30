// src/stores/scannerPortStore.js
import { defineStore } from "pinia";
import { getSocket } from "@/composables/socket";

export const useScannerPortStore = defineStore("scannerPort", {
  state: () => ({
    timeInInfo: null,
    timeOutInfo: null,
    unassignedScanners: [],
    socket: null,
    isPortStatusModalOpen: false,
  }),
  actions: {
    initializeSocket() {
      this.socket = getSocket();
      if (!this.socket) {
        console.error("Socket connection not initialized.");
        return;
      }
      this.setupSocketListeners();
      // On socket init, sync assignments from localStorage if they exist.
      const stored = localStorage.getItem("scannerAssignments");
      if (stored) {
        const assignments = JSON.parse(stored);
        this.socket.emit("syncScannerAssignments", assignments);
      }
    },
    setupSocketListeners() {
      if (!this.socket) return;

      this.socket.on("scannerDetected", (data) => {
        if (!data.assigned) {
          const exists = this.unassignedScanners.find(
            (scanner) => scanner.uniqueKey === data.uniqueKey
          );
          if (!exists) {
            this.unassignedScanners.push(data);
          }
        } else {
          this.unassignedScanners = this.unassignedScanners.filter(
            (scanner) => scanner.uniqueKey !== data.uniqueKey
          );
          if (data.role === "Time In") {
            this.timeInInfo = data;
          } else if (data.role === "Time Out") {
            this.timeOutInfo = data;
          }
        }
      });

      this.socket.on("scannerAssigned", (data) => {
        if (data.role === "Time In") {
          this.timeInInfo = { ...data, online: true, role: "Time In" };
        } else if (data.role === "Time Out") {
          this.timeOutInfo = { ...data, online: true, role: "Time Out" };
        }
        this.unassignedScanners = this.unassignedScanners.filter(
          (scanner) => scanner.uniqueKey !== data.uniqueKey
        );
      });

      this.socket.on("scannerAssignmentError", (data) => {
        console.error("Assignment Error:", data.message);
      });

      this.socket.on("scannerRemoved", (data) => {
        if (this.timeInInfo && this.timeInInfo.uniqueKey === data.uniqueKey) {
          this.timeInInfo = null;
        }
        if (this.timeOutInfo && this.timeOutInfo.uniqueKey === data.uniqueKey) {
          this.timeOutInfo = null;
        }
      });

      this.socket.on("scannerDisconnected", (data) => {
        this.unassignedScanners = this.unassignedScanners.filter(
          (scanner) => scanner.uniqueKey !== data.uniqueKey
        );
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
    removeAssignment(uniqueKey) {
      if (this.socket) {
        this.socket.emit("removeAssignment", { uniqueKey });
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
