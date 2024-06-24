/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.{html,js,php}"],
    theme: {
      extend: {
        fontFamily: {
          'mochiy-pop-one': ['"Mochiy Pop One"', 'sans-serif'],
          'm-plus-1': ['"M PLUS 1 Code"', 'sans-serif'],
        },
      },
    },
    plugins: [],
  }