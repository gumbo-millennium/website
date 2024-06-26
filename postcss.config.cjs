/* eslint-disable quote-props */
const autoprefixer = require( 'autoprefixer')
const cssnano = require( 'cssnano')
const pixrem = require( 'pixrem')
const postcssCalc = require( 'postcss-calc')
const postcssImport = require( 'postcss-import')
const postcssRem = require( 'postcss-rem')
const postcssVariables = require( 'postcss-css-variables')
const tailwindcss = require('tailwindcss')

module.exports = ({ file, options, env }) => {
  const isProduction = env === 'production'
  const isMail = file?.basename === 'mail.css'

  const plugins = [
    postcssImport(),
    tailwindcss,
    postcssCalc({}),
    autoprefixer(),
    // cssnano
  ]

  // Inline variables if required
  if (isMail && isProduction) {
    // Remove dark, variables and convert rem to px
    plugins.splice(plugins.length - 1, 0, [
      postcssVariables({
        preserve: false,
      }),
      postcssRem({ convert: 'px' }),
      pixrem({ replace: false }),
    ])
  }

  if (isProduction) {
    // Add cssnano as last
    plugins.push(cssnano(options?.cssnano))
  }

  return {
    parser: false,
    plugins: plugins,
  }
}
