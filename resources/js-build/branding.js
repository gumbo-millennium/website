/**
 * Gumbo configurations
 */

// Get default theme
const defaultTheme = require('tailwindcss/defaultTheme')

// Shorthand some colors
const buildNightModeCapable = (color) => {
  return {
    'primary-1': `var(--color-${color}-primary-1)`,
    'primary-2': `var(--color-${color}-primary-2)`,
    'primary-3': `var(--color-${color}-primary-3)`,
    'secondary-1': `var(--color-${color}-secondary-1)`,
    'secondary-2': `var(--color-${color}-secondary-2)`,
    'secondary-3': `var(--color-${color}-secondary-3)`
  }
}

const brandColors = {
  50: '#cce5cc',
  100: '#a3d0a3',
  200: '#7abb7a',
  300: '#52a752',
  400: '#299229',
  500: '#007d00',
  600: '#006900',
  700: '#005f00',
  800: '#004b00',
  900: '#003700'
}

const colors = {
  source: {
    // Default colors
    gray: defaultTheme.colors.gray,
    red: defaultTheme.colors.red,
    orange: defaultTheme.colors.orange,
    green: defaultTheme.colors.green,
    blue: defaultTheme.colors.blue,

    // Our colors
    brand: brandColors
  },

  // Disable some colors
  yellow: {},
  teal: {},
  indigo: {},
  purple: {},
  pink: {},

  // Add night-mode-capable primaries
  light: 'var(--color-light)',
  dark: 'var(--color-dark)',

  // Add night-mode-capable colors
  gray: buildNightModeCapable('gray'),
  red: buildNightModeCapable('red'),
  orange: buildNightModeCapable('orange'),
  green: buildNightModeCapable('green'),
  blue: buildNightModeCapable('blue'),
  brand: buildNightModeCapable('brand')
}

module.exports = {
  colors: colors,
  plugins: [
    require('@tailwindcss/custom-forms')
  ]
}
