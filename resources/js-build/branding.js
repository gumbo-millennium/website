/**
 * Gumbo configurations
 */

// Get default theme
const Color = require('color')
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

const grayColors = {
  50: '#fafafa',
  100: '#f5f5f5',
  200: '#eeeeee',
  300: '#e0e0e0',
  400: '#bdbdbd',
  500: '#9e9e9e',
  600: '#757575',
  700: '#616161',
  800: '#424242',
  900: '#212121'
}

const brandColors = {
  50: '#acf097',
  100: '#86d376',
  200: '#73c465',
  300: '#60b554',
  400: '#4ca643',
  500: '#268922',
  600: '#137a11',
  700: '#006b00',
  800: '#005200',
  900: '#003900'
}

const brandColorsAlternative = {}

const colorShades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900]

colorShades.forEach(name => {
  brandColorsAlternative[name] = Color(brandColors[name]).rotate(180).hex()
})

const colors = {
  source: {
    // Default colors
    red: defaultTheme.colors.red,
    orange: defaultTheme.colors.yellow,
    green: defaultTheme.colors.green,
    blue: defaultTheme.colors.blue,

    // Our colors
    gray: grayColors,
    brand: brandColors,

    // April fools ;)
    'brand-alt': brandColorsAlternative
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
}
