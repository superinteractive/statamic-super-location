import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    prefix: "sup-",
    content: ["./resources/js/**/*.{js,vue}", "./resources/views/**/*.antlers.html"],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
        },
    },
    darkMode: "class",
};
