/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primario: '#0d9488', // Verde esmeralda
        fondo: '#f3f4f6'
      }
    },
  },
  plugins: [],
}