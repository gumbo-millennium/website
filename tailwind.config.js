/**
 * Tailwind Configuration
 */

//  Load our settings
const { brand } = require('./resources/js-build/branding')
const defaultTheme = require('tailwindcss/defaultTheme')

// Build configs
module.exports = {
  mode: 'jit',

  content: [
    'resources/views/**/*.blade.php',
    'resources/assets/yaml/**/*.yaml',
    'app/**/*.php',
  ],

  safelist: {
    deep: ['prose'],
  },

  theme: {
    container: {
      center: true,
    },
    extend: {
      fontFamily: {
        title: ['Poppins'].concat(defaultTheme.fontFamily.sans),
      },
      fontSize: {
        huge: '8rem',
      },
      colors: {
        brand: brand,
      },
      backgroundPosition: {
        center: 'center',
      },
      cursor: {
        default: 'default',
        pointer: 'pointer',
      },
      fontWeight: {
        light: '300',
        normal: '400',
        bold: '700',
      },
      letterSpacing: {},
      objectPosition: {
        center: 'center',
        top: 'top',
      },
    },
  },

  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/aspect-ratio'),
  ],

  corePlugins: {
    // Effects
    backgroundBlendMode: false,

    // Disable all filters
    filter: false,
    blur: false,
    brightness: false,
    contrast: false,
    grayscale: false,
    hueRotate: false,
    invert: false,
    saturate: false,
    sepia: false,

    // Disable all backdrop filters
    backdropFilter: false,
    backdropBlur: false,
    backdropBrightness: false,
    backdropContrast: false,
    backdropGrayscale: false,
    backdropHueRotate: false,
    backdropInvert: false,
    backdropSaturate: false,
    backdropSepia: false,

    // Disable table layout
    tableLayout: false,
  },
}
