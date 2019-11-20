// Base frame
const mix = require('laravel-mix')

// Plugins and loaders
const WebpackStrip = require('strip-loader')

// Inline Plugins
require('laravel-mix-versionhash')
require('laravel-mix-purgecss')
require('./resources/js-build/plugins')

// Plugins for PostCSS
const postCssPlugins = [
  require('postcss-import'),
  require('tailwindcss')
]

// Compile stylesheets
mix.postCss('resources/css/app.css', 'public/css/app.css', postCssPlugins)
mix.postCss('resources/css/beta.css', 'public/css/beta.css', postCssPlugins)

// Copy files
mix.copy([
  'resources/assets/images/**/*.{jpg,png,jpeg,svg}',
  'resources/assets/svg/**/*.svg'
], 'public/images')

// Add plugins
mix.gumbo({
  eslint: {
    standard: true
  }
})

// Production code
if (mix.inProduction()) {
  // Enable purgecss
  mix.purgeCss()

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

// Let Vue use the production runtime in production.
// Allows us to use a very, very strict CSP
if (mix.inProduction()) {
  mix.webpackConfig({
    resolve: {
      alias: {
        vue$: 'vue/dist/vue.runtime.js'
      }
    }
  })
}
