/* eslint-disable quote-props */
// Plugins
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')
const postcssCalc = require('postcss-calc')
const postcssImport = require('postcss-import')
const postcssRem = require('postcss-rem')
const purgecss = require('@fullhuman/postcss-purgecss')
const tailwindcss = require('tailwindcss')

// Configs
const purgeExtensions = ['html', 'js', 'jsx', 'ts', 'tsx', 'php', 'vue']

module.exports = ({ file, options, env }) => ({
  parser: false,
  plugins: [
    postcssImport(),
    tailwindcss(),
    purgecss({
      content: [`app/**/*.php`].concat(
        purgeExtensions.map(ext => `resources/**/*.${ext}`)
      ),
      extractors: [
        {
          extractor: class {
            static extract (content) {
              return content.match(/[a-zA-Z0-9-:_/]+/g) || []
            }
          },
          extensions: purgeExtensions
        }
      ]
    }),
    postcssCalc({}),
    postcssRem({ convert: (file.basename === 'pdf.css' ? 'px' : 'rem') }),
    autoprefixer(),
    cssnano(env === 'production' ? options.cssnano : false)
  ]
})
