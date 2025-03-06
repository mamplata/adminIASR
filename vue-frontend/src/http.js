// src/http.js
import axios from "axios";

const apiUrl = import.meta.env.VITE_API_URL;

const http = axios.create({
  baseURL: apiUrl, // Use the environment variable
  headers: {
    "Content-Type": "application/json",
  },
});

export default http;
