/* eslint-disable quote-props */
// OS-level
const path = require('path')

// Plugins
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')
const postcssCalc = require('postcss-calc')
const postcssImport = require('postcss-import')
const postcssRem = require('postcss-rem')
const postcssVariables = require('postcss-css-variables')
const purgecss = require('@fullhuman/postcss-purgecss')
const responsiveImages = require('./resources/js-build/postcss-responsive-image')
const tailwindcss = require('tailwindcss')

module.exports = ({ file, options, env }) => {
  const remConfig = {
    convert: 'rem'
  }

  // Mail needs pixels, instead of rem
  if (file.basename === 'mail.css') {
    remConfig.convert = 'px'
  }

  const plugins = [
    postcssImport(),
    tailwindcss(),
    // purgecss
    responsiveImages(),
    postcssCalc({}),
    postcssRem(remConfig),
    autoprefixer()
    // cssnano
  ]

  // Inline variables if required
  if (file.basename === 'mail.css') {
    plugins.splice(5, 0, postcssVariables())
  }

  // Add production systems
  if (env === 'production') {
    // Add purgecss as 3rd
    plugins.splice(2, 0, purgecss({
      content: [
        'app/**/*.php',
        'config/**/*.php',
        'resources/views/*.blade.php',
        'resources/views/**/*.blade.php',
        'resources/assets/html/**/*.html',
        'resources/**/*.js',
        'resources/**/*.vue'
      ],
      extractors: [
        {
          extractor: class {
            static extract (content) {
              return content.match(/[a-zA-Z0-9-:_/]+/g) || []
            }
          },
          extensions: ['php', 'html', 'js', 'vue']
        }
      ]
    }))

    // Add cssnano as last
    plugins.push(cssnano(options.cssnano))
  }

  return {
    parser: false,
    plugins: plugins
  }
}
