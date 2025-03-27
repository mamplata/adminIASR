import { defineStore } from "pinia";
import HTTP from "@/http";
import { getSocket } from "@/composables/socket";
import { watch } from "vue";
import { useScannerPortStore } from "@/stores/scannerPortStore";
import { useDeviceStore } from "@/stores/deviceStore";

export const useTimeOutStore = defineStore("timeOut", {
  state: () => ({
    // Only keep the data needed for scanning and logging.
    scannedStudent: null,
    isReadingNfc: false,
    isLoading: false,
    nfcData: null,
    nfcError: "",
    timeOutScanner: null,
    scannerStatusLoading: true,
    socketConnected: false,
    socket: null,
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

      // Initialize the scanner port store and device store.
      const scannerPortStore = useScannerPortStore();
      scannerPortStore.initializeSocket();
      const deviceStore = useDeviceStore();
      this.deviceFingerprint = deviceStore.deviceFingerprint;

      // Watch for changes in the Time Out scanner status.
      watch(
        () => scannerPortStore.timeOutInfo,
        (newVal) => {
          this.timeOutScanner = newVal;
          this.scannerStatusLoading = false;
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

      this.socket.on("cardRead", (data) => {
        this.nfcData = data;
        this.processScannedCard(data);
      });

      this.socket.on("readFailed", (data) => {
        console.error("NFC Read Failed:", data);
        this.nfcError = data;
        this.isReadingNfc = false;
        this.scannedStudent = null;
        // Retry reading after a delay.
        setTimeout(() => {
          this.nfcError = "";
          this.readNfcCard();
        }, 3000);
      });
    },
    readNfcCard() {
      // Wait until the scanner status is loaded and socket is connected.
      if (this.scannerStatusLoading || !this.socketConnected) {
        setTimeout(() => this.readNfcCard(), 500);
        return;
      }
      // Ensure the Time Out scanner is available and online.
      if (!(this.timeOutScanner && this.timeOutScanner.online)) {
        console.log("Time Out scanner is not available. Scanning disabled.");
        return;
      } else {
        console.log("dasda");
      }
      // Prevent multiple read attempts.
      if (this.isReadingNfc) return;
      this.isReadingNfc = true;
      this.nfcData = null;
      console.log("Requesting to read NFC card for Time Out...");
      if (this.socket) {
        // Only emit the readCard event.
        this.socket.emit("readCard");
      } else {
        console.error("Socket connection not established");
      }
    },
    async processScannedCard(card) {
      this.isLoading = true;
      let studentData = null;
      try {
        // Scan and retrieve student information.
        const response = await HTTP.post(
          "/api/card/scan",
          { uid: card.uid, data: card.data },
          { withCredentials: true }
        );
        studentData = response.data.student;
        this.scannedStudent = studentData;

        // Log a successful Time Out entry.
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
          console.error("Failed to log entry:", logError);
        }
        this.isLoading = false;
        // After a delay, clear the student info and restart NFC reading.
        setTimeout(() => {
          this.scannedStudent = null;
          this.readNfcCard();
        }, 5000);
      } catch (err) {
        const errorMessage =
          err.response?.data?.error || "An error occurred during card scan.";
        // Log unauthorized access if applicable.
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
            console.error("Failed to log unauthorized access:", unauthLogError);
          }
        } else {
          // Log a failed entry.
          try {
            await HTTP.post("/api/entry-logs", {
              device_id: this.deviceFingerprint,
              uid: card.uid,
              student_id: studentData ? studentData.studentId.toString() : "",
              time_type: "OUT",
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
        // Retry reading after a delay.
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
