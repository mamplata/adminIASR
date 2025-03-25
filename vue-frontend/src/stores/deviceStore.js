// src/stores/deviceStore.js
import { defineStore } from "pinia";
import HTTP from "@/http";
import { initializeSocket } from "@/composables/socket";

export const useDeviceStore = defineStore("device", {
  state: () => ({
    isRegistered: false,
    deviceName: "",
    deviceFingerprint: "",
    checkingRegistration: true,
  }),
  actions: {
    async checkRegistration() {
      try {
        const response = await HTTP.get("/api/device/status", {
          withCredentials: true,
        });
        if (response.data) {
          if (response.data.device_name) {
            this.deviceName = response.data.device_name;
          }
          if (response.data.device_fingerprint) {
            this.deviceFingerprint = response.data.device_fingerprint;
            initializeSocket(this.deviceFingerprint);
          }
          this.isRegistered = true;
        }
      } catch (error) {
        this.isRegistered = false;
      } finally {
        this.checkingRegistration = false;
      }
    },
    async registerDevice(shortCode) {
      try {
        const response = await HTTP.post(
          "/api/device/register",
          { short_code: shortCode },
          { withCredentials: true }
        );
        if (response.data && response.data.success) {
          if (response.data.device_fingerprint) {
            this.deviceFingerprint = response.data.device_fingerprint;
            initializeSocket(this.deviceFingerprint);
          }
          this.deviceName = response.data.device_name || "";
          this.isRegistered = true;
          return { success: true };
        }
      } catch (error) {
        return {
          success: false,
          message:
            error.response?.data?.message || "An unexpected error occurred.",
        };
      }
    },
  },
});
