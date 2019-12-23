/* eslint-disable quote-props */
// OS-level
const path = require('path')

// Plugins
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')
const postcssCalc = require('postcss-calc')
const postcssImport = require('postcss-import')
const postcssRem = require('postcss-rem')
const purgecss = require('@fullhuman/postcss-purgecss')
const responsiveImages = require('./resources/postcss/responsive-image')
const tailwindcss = require('tailwindcss')

module.exports = ({ file, options, env }) => {
  const plugins = [
    postcssImport(),
    tailwindcss(),
    // purgecss
    responsiveImages(),
    postcssCalc({}),
    postcssRem({ convert: 'rem' }),
    autoprefixer()
    // cssnano
  ]

  if (env === 'production') {
    // Add purgecss as 3rd
    plugins.splice(2, 0, purgecss({
      content: [
        'app/**/*.php',
        'config/**/*.php',
        'resources/**/*.blade.php',
        'resources/**/*.html',
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
