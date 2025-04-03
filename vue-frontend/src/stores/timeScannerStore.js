// src/stores/timeScannerStore.js
import { defineStore } from "pinia";
import HTTP from "@/http";
import { getSocket } from "@/composables/socket";
import { watch } from "vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";
import { useDeviceStore } from "@/stores/deviceStore";
import { useToast } from "vue-toastification";

const toast = useToast();

export const useTimeScannerStore = defineStore("timeScanner", {
  state: () => ({
    // Shared scanning state for UI (only used for Time IN)
    scannedStudent: null,
    selectedDepartment: "GENERAL",
    schedule: [],
    scheduleError: "",
    // These UI flags are only used for Time IN
    isReadingNfc: false,
    isLoading: false,
    nfcData: null,
    nfcError: "",
    // Separate scanner assignments for each role
    timeInScanner: null,
    timeOutScanner: null,
    // General state
    scannerStatusLoading: true,
    socketConnected: false,
    socket: null,
    deviceFingerprint: "",
    // Flag to log Time OUT online only once
    timeOutOnlineLogged: false,
  }),
  actions: {
    initializeSocket() {
      this.socket = getSocket();
      if (!this.socket) {
        console.error("Socket connection is not initialized.");
        return;
      }
      this.setupSocketListeners();

      // Initialize external stores.
      const scannerPortStore = useScannerPortStore();
      scannerPortStore.initializeSocket();
      const deviceStore = useDeviceStore();
      this.deviceFingerprint = deviceStore.deviceFingerprint;

      // Watch for changes in Time IN scanner assignment.
      watch(
        () => scannerPortStore.timeInInfo,
        (newVal) => {
          this.timeInScanner = newVal;
          this.scannerStatusLoading = false;
          if (newVal && newVal.online) {
            this.readNfcCard("IN");
          }
        },
        { immediate: true }
      );

      // Watch for changes in Time OUT scanner assignment.
      watch(
        () => scannerPortStore.timeOutInfo,
        (newVal) => {
          this.timeOutScanner = newVal;
          this.scannerStatusLoading = false;
          if (newVal && newVal.online) {
            if (!this.timeOutOnlineLogged) {
              console.log("Time OUT scanner is online.");
              this.timeOutOnlineLogged = true;
            }
            this.readNfcCard("OUT");
          }
        },
        { immediate: true }
      );

      // Optionally trigger both flows after a timeout.
      setTimeout(() => {
        console.log(
          "Time Scanner store timeout triggered readNfcCard for both roles."
        );
        this.readNfcCard("IN");
        this.readNfcCard("OUT");
      }, 3000);
    },
    setupSocketListeners() {
      if (!this.socket) return;
      this.socket.on("connect", () => {
        console.log("Socket connected (Time Scanner store).");
        this.socketConnected = true;
      });
      // Listen for role-specific card read events.
      this.socket.on("timeInCardRead", (data) => {
        console.log("timeInCardRead", data);
        this.nfcData = data;
        this.processScannedCard(data, "IN");
      });
      this.socket.on("timeOutCardRead", (data) => {
        console.log("timeOutCardRead", data);
        console.log("Time OUT event received:", data);
        this.nfcData = data;
        this.processScannedCard(data, "OUT");
      });
      this.socket.on("readFailed", (data) => {
        console.error("NFC Read Failed:", data);
        this.nfcError = data;
        this.isReadingNfc = false;
        // Clear UI (for Time IN) scanned student.
        this.scannedStudent = null;
        // Retry reading after a delay for both roles.
        setTimeout(() => {
          this.nfcError = "";
          this.readNfcCard("IN");
          this.readNfcCard("OUT");
        }, 3000);
      });
    },
    /**
     * Initiates an NFC read for the given role ("IN" or "OUT").
     */
    readNfcCard(role) {
      if (this.scannerStatusLoading || !this.socketConnected) {
        setTimeout(() => this.readNfcCard(role), 500);
        return;
      }
      if (role === "IN") {
        if (!(this.timeInScanner && this.timeInScanner.online)) {
          console.log(
            "Time IN scanner is not available. Scanning disabled for IN."
          );
          return;
        }
      } else if (role === "OUT") {
        if (!(this.timeOutScanner && this.timeOutScanner.online)) {
          console.log(
            "Time OUT scanner is not available. Scanning disabled for OUT. Retrying..."
          );
          setTimeout(() => this.readNfcCard(role), 1000);
          return;
        }
      }
      if (this.isReadingNfc) return;
      this.isReadingNfc = true;
      this.nfcData = null;
      console.log(`Requesting to read NFC card for ${role}...`);
      if (this.socket) {
        this.socket.emit("readCard");
        if (role === "IN") {
          this.socket.emit("getStoredAssignments");
        }
      } else {
        console.error(
          "Socket connection not established (Time Scanner store)."
        );
      }
    },
    /**
     * Processes the scanned card data.
     * For "IN", fetches student info, schedules, and updates UI.
     * For "OUT", fetches student info to obtain the student ID, logs the event,
     * but does not update UI-related properties.
     * @param {Object} card - The card data from the socket.
     * @param {string} role - "IN" or "OUT".
     */
    async processScannedCard(card, role) {
      // For OUT, process without triggering UI loading.
      if (role === "OUT") {
        console.log(
          "Processing Time OUT event. Fetching student info for logging."
        );
        try {
          const response = await HTTP.post(
            "/api/card/scan",
            { uid: card.uid, data: card.data },
            { withCredentials: true }
          );
          const studentData = response.data.student;
          console.log(
            "Time OUT processing complete. Student ID:",
            studentData.studentId
          );
          // Show a toast notification with the student ID
          toast.success(`Time OUT: Student ID ${studentData.studentId}`, {
            timeout: 5000,
          });
          try {
            await HTTP.post("/api/entry-logs", {
              device_id: this.deviceFingerprint,
              uid: card.uid,
              student_id: studentData.studentId.toString(),
              time_type: "OUT",
              status: "Success",
              failure_reason: null,
            });
          } catch (logError) {
            console.error("Failed to log Time OUT entry:", logError);
          }

        } catch (err) {
          const errorMessage =
            err.response?.data?.error ||
            "An error occurred during Time OUT card scan.";
          if (
            errorMessage === "Unauthorized access." ||
            errorMessage === "Card is not activated"
          ) {
            try {
              await HTTP.post("/api/unauthorized-logs", {
                device_id: this.deviceFingerprint,
                uid: card.uid,
                time_type: "OUT",
                reason: errorMessage,
              });
            } catch (unauthLogError) {
              console.error(
                "Failed to log unauthorized access for OUT:",
                unauthLogError
              );
            }
          } else {
            try {
              await HTTP.post("/api/entry-logs", {
                device_id: this.deviceFingerprint,
                uid: card.uid,
                student_id: "",
                time_type: "OUT",
                status: "Failure",
                failure_reason: errorMessage,
              });
            } catch (logError) {
              console.error("Failed to log failure entry for OUT:", logError);
            }
          }
        } finally {
          // For OUT, do not update UI; just restart reading.
          setTimeout(() => {
            this.readNfcCard("OUT");
          }, 1000);
          this.isReadingNfc = false;
        }
        return;
      }

      // Process for Time IN:
      this.isLoading = true;
      let studentData = null;
      try {
        const response = await HTTP.post(
          "/api/card/scan",
          { uid: card.uid, data: card.data },
          { withCredentials: true }
        );
        studentData = response.data.student;

        // Update UI immediately with scanned student data.
        this.scannedStudent = studentData;
        // Clear any previous schedule or error
        this.schedule = [];
        this.scheduleError = "";

        // Log the successful scan asynchronously.
        HTTP.post("/api/entry-logs", {
          device_id: this.deviceFingerprint,
          uid: card.uid,
          student_id: studentData.studentId.toString(),
          time_type: "IN",
          status: "Success",
          failure_reason: null,
        }).catch((logError) => {
          console.error("Failed to log IN entry:", logError);
        });

        // Initiate schedule fetching without waiting for it to complete.
        this.fetchStudentSchedule(studentData.studentId);

        // Update selected department immediately.
        this.selectedDepartment = `${studentData.department}: ${studentData.program}`;
        this.isLoading = false;

        // Optionally, clear the student display after a delay.
        setTimeout(() => {
          this.scannedStudent = null;
          // If needed, you can also clear schedule-related state here.
          this.schedule = [];
          this.scheduleError = "";
          this.readNfcCard("IN");
        }, 5000);
      } catch (err) {
        const errorMessage =
          err.response?.data?.error || "An error occurred during IN card scan.";
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
            console.error(
              "Failed to log unauthorized access for IN:",
              unauthLogError
            );
          }
        } else {
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
            console.error("Failed to log failure entry for IN:", logError);
          }
        }
        this.isLoading = false;
        this.nfcError = errorMessage;
        this.scannedStudent = null;
        setTimeout(() => {
          this.nfcError = "";
          this.readNfcCard("IN");
        }, 5000);
      } finally {
        this.isReadingNfc = false;
      }
    },

    /**
     * Fetches the student's schedule and updates the UI.
     */
    async fetchStudentSchedule(studentId) {
      try {
        const scheduleResponse = await HTTP.get(`/api/fetch-schedule/${studentId}`);
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
      } catch (err) {
        console.error("Error fetching schedule:", err);
        this.schedule = [];
        this.scheduleError = "Failed to load schedule.";
      }
    }

  },
});
