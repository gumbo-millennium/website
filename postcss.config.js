/* eslint-disable quote-props */
// OS-level
const path = require('path')

// Plugins
const autoprefixer = require('autoprefixer')
const cssnano = require('cssnano')
const postcssCalc = require('postcss-calc')
const postcssImport = require('postcss-import')
const postcssRem = require('postcss-rem')
const postcssUrl = require('postcss-url')
const purgecss = require('@fullhuman/postcss-purgecss')
const tailwindcss = require('tailwindcss')

// Configs
const sourceImagePath = path.resolve(__dirname, 'resources/assets/images')
const destImagePath = path.resolve(__dirname, 'public/assets')
const purgeExtensions = ['html', 'js', 'jsx', 'ts', 'tsx', 'php', 'vue']

module.exports = ({ file, options, env }) => ({
  parser: false,
  plugins: [
    postcssImport(),
    tailwindcss(),
    purgecss({
      content: [`${__dirname}/app/**/*.php`].concat(
        purgeExtensions.map(ext => `${__dirname}/resources/**/*.${ext}`)
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
