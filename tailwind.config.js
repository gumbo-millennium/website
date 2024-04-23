/**
 * Tailwind Configuration
 */

//  Load our settings
import { brand } from './resources/js-build/branding.js'
import defaultTheme from 'tailwindcss/defaultTheme'
import formsPlugin from '@tailwindcss/forms'
import aspectRatioPlugin from '@tailwindcss/aspect-ratio'

// Build configs
module.exports = {
  mode: 'jit',

  content: [
    'resources/views/**/*.blade.php',
    'resources/yaml/**/*.yaml',
    'resources/js/**/*.{js,vue}',
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
    formsPlugin,
    aspectRatioPlugin,
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
