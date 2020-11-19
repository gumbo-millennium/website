/* eslint-disable quote-props */
// Plugins
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')
const pixrem = require('pixrem')
const postcssCalc = require('postcss-calc')
const postcssImport = require('postcss-import')
const postcssRem = require('postcss-rem')
const postcssVariables = require('postcss-css-variables')
const removeDarkMode = require('./resources/js-build/postcss-remove-darkmode')
const responsiveImages = require('./resources/js-build/postcss-responsive-image')
const tailwindcss = require('tailwindcss')

module.exports = ({ file, options, env }) => {
  const isProduction = env === 'production'
  const remConfig = { convert: 'rem' }

  // Mail needs pixels, instead of rem
  if (file.basename === 'mail.css') {
    remConfig.convert = 'px'
  }

  const plugins = [
    postcssImport(),
    tailwindcss(),
    responsiveImages(),
    postcssCalc({}),
    postcssRem(remConfig),
    autoprefixer()
    // cssnano
  ]

  // Inline variables if required
  if (file.basename === 'mail.css') {
    // Remove dark
    plugins.splice(plugins.length - 1, 0, removeDarkMode())

    // Remove unsuported variables
    if (isProduction) {
      plugins.splice(plugins.length - 1, 0, postcssVariables())
    }

    // Add rem to px
    plugins.splice(plugins.length - 1, 0, pixrem({ replace: false }))
  }

  if (isProduction) {
    // Add cssnano as last
    plugins.push(cssnano(options.cssnano))
  }

  return {
    parser: false,
    plugins: plugins
  }
}
