import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import path from "path";
export default defineConfig({
  plugins: [vue()], // Remove TailwindCSS from here
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "src"), // Ensure '@' points to 'src'
    },
  },
});
