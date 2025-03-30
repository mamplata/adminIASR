// src/stores/timeInStore.js
import { defineStore } from "pinia";
import HTTP from "@/http";
import { getSocket } from "@/composables/socket";
import { watch } from "vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";
import { useDeviceStore } from "@/stores/deviceStore";

export const useTimeInStore = defineStore("timeIn", {
  state: () => ({
    scannedStudent: null,
    selectedDepartment: "GENERAL",
    schedule: [],
    scheduleError: "",
    isReadingNfc: false,
    isLoading: false,
    nfcData: null,
    nfcError: "",
    timeInScanner: null,
    scannerStatusLoading: true,
    socketConnected: false,
    socket: null,
    // It is useful to have the deviceFingerprint available if needed for API calls.
    deviceFingerprint: "",
  }),
  actions: {
    initializeSocket() {
      this.socket = getSocket();
      if (!this.socket) {
        console.error("Socket connection is not initialized.");
        return;
      }
      this.setupSocketListeners();
      // Also, initialize scannerPortStore's socket and sync state.
      const scannerPortStore = useScannerPortStore();
      scannerPortStore.initializeSocket();
      const deviceStore = useDeviceStore();

      // Get the device fingerprint directly from the deviceStore.
      this.deviceFingerprint = deviceStore.deviceFingerprint;

      // Set up a watcher to sync timeInScanner from scannerPortStore.
      watch(
        () => scannerPortStore.timeInInfo,
        (newVal) => {
          this.timeInScanner = newVal;
          this.scannerStatusLoading = false; // assignment means loaded
          // If a Time In scanner is assigned and online, trigger NFC reading.
          if (newVal && newVal.online) {
            this.readNfcCard();
          }
        },
        { immediate: true }
      );
    },
    setupSocketListeners() {
      if (!this.socket) return;

      this.socket.on("connect", () => {
        console.log("Socket connected");
        this.socketConnected = true;
      });

      this.socket.on("timeInCardRead", (data) => {
        this.nfcData = data;
        this.processScannedCard(data);
      });

      this.socket.on("readFailed", (data) => {
        console.error("❌ NFC Read Failed:", data);
        this.nfcError = data;

        this.isReadingNfc = false;
        this.scannedStudent = null;
        this.schedule = [];
        setTimeout(() => {
          this.nfcError = "";
          this.readNfcCard();
        }, 3000);
      });
    },
    readNfcCard() {
      // Wait until scanner status is loaded and socket is connected
      if (this.scannerStatusLoading || !this.socketConnected) {
        setTimeout(() => this.readNfcCard(), 500);
        return;
      }
      // Ensure scanning is enabled
      if (!(this.timeInScanner && this.timeInScanner.online)) {
        console.log("Time In scanner is not available. Scanning disabled.");
        return;
      }
      if (this.isReadingNfc) return;
      this.isReadingNfc = true;
      this.nfcData = null;
      console.log("📡 Requesting to read NFC card...");
      if (this.socket) {
        this.socket.emit("readCard");
        this.socket.emit("getStoredAssignments");
      } else {
        console.error("Socket connection not established");
      }
    },
    async processScannedCard(card) {
      this.isLoading = true;
      let studentData = null;
      try {
        const response = await HTTP.post(
          "/api/card/scan",
          { uid: card.uid, data: card.data },
          { withCredentials: true }
        );
        studentData = response.data.student;

        // Log a successful entry
        try {
          await HTTP.post("/api/entry-logs", {
            device_id: this.deviceFingerprint,
            uid: card.uid,
            student_id: studentData.studentId.toString(),
            time_type: "IN",
            status: "Success",
            failure_reason: null,
          });
        } catch (logError) {
          console.error("Failed to log entry:", logError);
        }

        const scheduleResponse = await HTTP.get(
          `/api/fetch-schedule/${studentData.studentId}`
        );
        if (
          scheduleResponse.data.schedule &&
          scheduleResponse.data.schedule.length > 0
        ) {
          const allSchedules = Array.isArray(scheduleResponse.data.schedule)
            ? scheduleResponse.data.schedule
            : [scheduleResponse.data.schedule];
          const todayName = new Date().toLocaleDateString("en-US", {
            weekday: "long",
          });
          const todaySchedules = allSchedules.filter((item) =>
            item.day.includes(todayName)
          );
          if (todaySchedules.length > 0) {
            this.schedule = todaySchedules;
          } else {
            this.schedule = [];
            this.scheduleError = "No schedule available for today.";
          }
        } else {
          this.schedule = [];
          this.scheduleError =
            scheduleResponse.data.message || "No schedule available.";
        }
        this.scannedStudent = studentData;

        // Now set the selected department after processing the schedule and student info
        this.selectedDepartment = `${studentData.department}: ${studentData.program}`;

        this.isLoading = false;
        setTimeout(() => {
          this.scannedStudent = null;
          this.schedule = [];
          this.scheduleError = "";
          this.readNfcCard();
        }, 5000);
      } catch (err) {
        const errorMessage =
          err.response?.data?.error || "An error occurred during card scan.";

        // Log unauthorized access if error matches one of the unauthorized messages
        if (
          errorMessage === "Unauthorized access." ||
          errorMessage === "Card is not activated"
        ) {
          try {
            await HTTP.post("/api/unauthorized-logs", {
              device_id: this.deviceFingerprint,
              uid: card.uid,
              time_type: "IN",
              reason: errorMessage,
            });
          } catch (unauthLogError) {
            console.error("Failed to log unauthorized access:", unauthLogError);
          }
        } else {
          // Otherwise, log as a failure entry
          try {
            await HTTP.post("/api/entry-logs", {
              device_id: this.deviceFingerprint,
              uid: card.uid,
              student_id: studentData ? studentData.studentId.toString() : "",
              time_type: "IN",
              status: "Failure",
              failure_reason: errorMessage,
            });
          } catch (logError) {
            console.error("Failed to log failure entry:", logError);
          }
        }
        this.isLoading = false;
        this.nfcError = errorMessage;
        this.scannedStudent = null;
        this.schedule = [];
        this.scheduleError = "";
        setTimeout(() => {
          this.nfcError = "";
          this.readNfcCard();
        }, 3000);
      } finally {
        this.isReadingNfc = false;
      }
    },
  },
});
