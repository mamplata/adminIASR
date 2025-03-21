const defaultTheme = require("tailwindcss/defaultTheme");

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.{vue,js,jsx}",
        "./node_modules/daisyui/dist/**/*.js", // Ensure DaisyUI is included
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                dark: {
                    "eval-0": "#151823",
                    "eval-1": "#222738",
                    "eval-2": "#2A2F42",
                    "eval-3": "#2C3142",
                    "base-100": "#ffffff",
                },
            },
        },
    },

    plugins: [
        require("@tailwindcss/forms"),
        require("daisyui"), // Make sure DaisyUI is included
    ],

    daisyui: {
        themes: ["light", "dark"], // Ensure DaisyUI adapts to dark mode
    },
};
