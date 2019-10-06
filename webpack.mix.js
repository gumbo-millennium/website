// Base frame
const mix = require('laravel-mix')

// Debug remover
const WebpackStrip = require('strip-loader')

// Inline Plugins
require('laravel-mix-versionhash')
require('./resources/js-build/plugins')

const postCssPlugins = [
  require('postcss-import'),
  require('tailwindcss')
]

// Compile Stylesheets
mix
  .postCss('resources/css/app.css', 'public/css/app.css', postCssPlugins)

// Copy files
mix.copy([
  'resources/assets/images/**/*.{jpg,png,jpeg,svg}'
], 'public/images')

// Add Siero plugins
mix.gumbo({
  eslint: {
    standard: true
  },
  spritemap: true,
  purgecss: false
})

// Production code
if (mix.inProduction()) {
  // Add custom hash
  mix.versionHash({
    length: 8
  })

  // Strip console debug messages
  mix.webpackConfig({
    module: {
      rules: [
        {
          test: /\.js$/,
          loader: WebpackStrip.loader('console.log', 'console.debug', 'console.info')
        }
      ]
    }
  })
}

// Add BrowserSync
mix.browserSync('127.0.0.1:13370')
