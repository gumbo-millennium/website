/**
 * Tailwind Configuration
 */

//  Load our settings
const gumboSettings = require('./tailwind.gumbo')

// Build configs
module.exports = {
  plugins: gumboSettings.plugins,
  theme: {
    container: {
      center: true
    },
    extend: {
      screens: {
        sm: '640px',
        md: '768px',
        lg: '1024px'
      },
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
      fontSize: {
        'huge': '8rem'
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
  variants: {
    accessibility: ['focus'],
    alignContent: [],
    alignItems: ['responsive'],
    alignSelf: [],
    appearance: [],
    backgroundAttachment: [],
    backgroundColor: ['hover', 'focus'],
    backgroundPosition: [],
    backgroundRepeat: [],
    backgroundSize: ['responsive'],
    borderCollapse: [],
    borderColor: ['hover', 'focus'],
    borderRadius: [],
    borderStyle: ['responsive'],
    borderWidth: ['responsive'],
    boxShadow: ['responsive', 'hover', 'focus'],
    cursor: [],
    display: ['responsive'],
    fill: [],
    flex: ['responsive'],
    flexDirection: ['responsive'],
    flexGrow: ['responsive'],
    flexShrink: ['responsive'],
    flexWrap: ['responsive'],
    float: [],
    fontFamily: [],
    fontSize: [],
    fontSmoothing: [],
    fontStyle: [],
    fontWeight: ['hover', 'focus'],
    height: ['responsive'],
    inset: [],
    justifyContent: ['responsive'],
    letterSpacing: [],
    lineHeight: ['responsive'],
    listStylePosition: [],
    listStyleType: [],
    margin: ['responsive'],
    maxHeight: ['responsive'],
    maxWidth: ['responsive'],
    minHeight: ['responsive'],
    minWidth: ['responsive'],
    objectFit: [],
    objectPosition: [],
    opacity: [],
    order: [],
    outline: ['focus'],
    overflow: ['responsive'],
    padding: ['responsive'],
    placeholderColor: ['focus'],
    pointerEvents: [],
    position: ['responsive'],
    resize: [],
    stroke: [],
    tableLayout: ['responsive'],
    textAlign: ['responsive'],
    textColor: ['responsive', 'hover', 'focus'],
    textDecoration: ['responsive', 'hover', 'focus'],
    textTransform: ['responsive'],
    userSelect: [],
    verticalAlign: [],
    visibility: [],
    whitespace: [],
    width: ['responsive'],
    wordBreak: [],
    zIndex: []
  }
}
