module.exports = {
  darkMode: 'class', // Enable class strategy for dark mode
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './resources/**/*.tsx',
    './resources/**/*.ts',
    './resources/**/*.jsx',
    './resources/**/*.html',
    './public/**/*.html',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: '#27A376', // Primary (style guide)
          dark: '#166D4E', // Primary 700
        },
        accent: {
          DEFAULT: '#FFCF24', // Secondary (style guide)
        },
      },
    },
  },
  plugins: [],
};
