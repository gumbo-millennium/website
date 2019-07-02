module.exports = {
  theme: {
    extend: {
      colors: {
        brand: {
          '50': '#cce5cc',
          '100': '#a3d0a3',
          '200': '#7abb7a',
          '300': '#52a752',
          '400': '#299229',
          '500': '#007d00',
          '600': '#006900',
          '700': '#005f00',
          '800': '#004b00',
          '900': '#003700'
        }
      }
    }
  },
  variants: {},
  plugins: [
    require('@tailwindcss/custom-forms')
  ]
}
