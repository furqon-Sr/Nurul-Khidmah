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
          DEFAULT: '#045F33', // Primary brand green
          dark: '#004524', // Gradient end / deep emerald
        },
        accent: {
          DEFAULT: '#F2C94C', // Gold accent used for highlights and active states
        },
      },
    },
  },
  plugins: [],
};
