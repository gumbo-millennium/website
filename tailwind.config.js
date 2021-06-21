/**
 * Tailwind Configuration
 */

//  Load our settings
const gumboSettings = require('./resources/js-build/branding')
const defaultTheme = require('tailwindcss/defaultTheme')

// Build configs
module.exports = {
  // mode: 'jit',

  purge: [
    'resources/views/**/*.blade.php',
    'app/**/*.php',
  ],

  theme: {
    container: {
      center: true
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
        16: '4rem'
      },
      fontFamily: {
        title: ['Poppins'].concat(defaultTheme.fontFamily.sans)
      },
      fontSize: {
        huge: '8rem'
      },
      colors: gumboSettings.colors,
      backgroundPosition: {
        center: 'center'
      },
      cursor: {
        default: 'default',
        pointer: 'pointer'
      },
      fontWeight: {
        light: '300',
        normal: '400',
        bold: '700'
      },
      letterSpacing: {},
      objectPosition: {
        center: 'center',
        top: 'top'
      },
      stroke: {},
      width: theme => ({
        auto: 'auto',
        ...theme('spacing'),
        '1/2': '50%',
        '1/3': '33.333333%',
        '2/3': '66.666667%',
        '1/4': '25%',
        '2/4': '50%',
        '3/4': '75%',
        '1/12': '8.333333%',
        '2/12': '16.666667%',
        '3/12': '25%',
        '4/12': '33.333333%',
        '5/12': '41.666667%',
        '6/12': '50%',
        '7/12': '58.333333%',
        '8/12': '66.666667%',
        '9/12': '75%',
        '10/12': '83.333333%',
        '11/12': '91.666667%',
        full: '100%',
        screen: '100vw'
      }),
      zIndex: {}
    }
  },

  corePlugins: {
    backgroundOpacity: false,
    textOpacity: false,
    borderOpacity: false,
    placeholderOpacity: false,
    divideOpacity: false
  },

  plugins: [
    require('@tailwindcss/forms'),
  ]
}
