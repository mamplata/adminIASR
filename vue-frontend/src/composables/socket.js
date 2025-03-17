// src/composables/socket.js
import { io } from 'socket.io-client';
import { ref } from 'vue';

const socket = ref(null);

/**
 * Initializes the socket connection using the provided deviceFingerprint.
 * If the socket is already initialized, it returns the existing instance.
 *
 * @param {string} deviceFingerprint - Unique identifier for the device.
 * @returns {object} - The Socket.IO client instance.
 */
export function initializeSocket(deviceFingerprint) {
    if (!socket.value) {
        socket.value = io('http://localhost:4000', {
            query: { deviceFingerprint }
        });
    }
    return socket.value;
}

/**
 * Returns the current socket instance.
 *
 * @returns {object|null} - The Socket.IO client instance or null if not initialized.
 */
export function getSocket() {
    return socket.value;
}

/**
 * Closes the current socket connection and resets the instance.
 */
export function closeSocket() {
    if (socket.value) {
        socket.value.disconnect();
        socket.value = null;
    }
}
