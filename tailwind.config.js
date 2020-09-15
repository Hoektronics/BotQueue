module.exports = {
  theme: {
    extend: {
      margin: {
        '1/2': '0.125rem'
      }
    }
  },
  variants: {},
  plugins: [],
  purge: [
      './resources/views/**/*.php',
      './resources/js/**/*.vue',
      './app/Services/*.php'
  ],
};
