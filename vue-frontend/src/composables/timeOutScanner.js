// src/composables/timeOutScanner.js
import { onMounted } from "vue";
import { useTimeOutStore } from "@/stores/timeOutStore";

export function timeOutScanner() {
  const timeOutStore = useTimeOutStore();

  onMounted(() => {
    // Initialize the socket and NFC scanning for Time Out.
    timeOutStore.initializeSocket();
  });

  return { timeOutStore };
}
