// src/http.ts
import axios from "axios";

const HTTP = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    "Content-Type": "application/json",
  },
  withCredentials: true, // Necessary for Sanctum cookie auth
});

export default HTTP;
