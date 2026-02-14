/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#00A6A6',
          dark: '#008585',
          light: '#33B8B8',
        },
        secondary: {
          DEFAULT: '#FF6B6B',
          dark: '#EE5253',
          light: '#FF8787',
        },
        accent: {
          DEFAULT: '#FFD93D',
          dark: '#FF9F43',
        },
      },
      fontFamily: {
        sans: ['Vazirmatn', 'Segoe UI', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
