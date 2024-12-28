/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{html,php,js}",
    "./features/*.{html,php,js}", 
    "./php/*.{html,php,js}", 
    "./js/*.{html,php,js}",
    "./features/component/**/*.php"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}