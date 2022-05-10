/**
 * Tailwind Configuration
 */

//  Load our settings
const gumboSettings = require('./resources/js-build/branding')
const defaultTheme = require('tailwindcss/defaultTheme')

// Build configs
module.exports = {
  mode: 'jit',

  purge: {
    content: [
      'resources/views/**/*.blade.php',
      'app/**/*.php',
    ],
    options: {
      safelist: {
        deep: ['prose'],
      },
      fontFace: true,
      keyframes: true,
      variables: true,
    },
  },

  theme: {
    container: {
      center: true,
    },
    extend: {
      spacing: {
        0: '0',
        1: '0.25rem',
        2: '0.5rem',
        4: '1rem',
        6: '1.5rem',
        8: '2rem',
        10: '2.5rem',
        12: '3rem',
        14: '3.5rem',
        16: '4rem',
      },
      fontFamily: {
        title: ['Poppins'].concat(defaultTheme.fontFamily.sans),
      },
      fontSize: {
        huge: '8rem',
      },
      colors: gumboSettings.colors,
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
    require('@tailwindcss/aspect-ratio')
  ],

  corePlugins: {
    // Disable gradient (which introduces a lot of variables)
    backgroundOrigin: false,
    backgroundImage: false,
    gradientColorStops: false,

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

    // Ring stuff
    ringColor: false,
    ringOffsetColor: false,
    ringOffsetWidth: false,
    ringOpacity: false,
    ringWidth: false,
  },
}
