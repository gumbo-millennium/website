/**
 * Webpack config, using Laravel Mix
 */

// node_modules dependencies
const mix = require('laravel-mix')
const glob = require('glob')

// Load versionhash fix (uses filenames instead of ?id=xxx)
require('laravel-mix-versionhash')

// Local dependencies
const { plugins: gumboPlugins, loaders: gumboLoaders } = require('./webpack.plugins')

// Make sure we version stuff using filename-based hashes
if (mix.inProduction()) {
  mix.versionHash()
}

// Configure PostCSS plugins
const postCssPlugins = [
  require('postcss-import'),
  require('tailwindcss')
]

// Configure Tailwind
mix
  .postCss('resources/css/app.css', 'public/css/app.css', postCssPlugins)

// Configure javascript
mix
  .js('resources/assets/js/theme.js', 'public/gumbo.js')
  .js('resources/assets/js/admin.js', 'public/gumbo-admin.js')

// Extract assets
mix.extract([
  'bootstrap',
  'dropzone',
  'gmaps',
  'jquery',
  'mobile-detect',
  'moment',
  'pikaday',
  'popper.js'
])

// Configure SCSS, also with separate vendor (bootstrap)
mix
  .sass(`resources/assets/sass/theme.scss`, 'public/gumbo.css')

// Always make jQuery and Popper available
mix.autoload({
  jquery: ['$', 'window.jQuery'],
  'popper.js': ['Popper']
})

// Copy all SVG files
mix.copyDirectory('resources/assets/svg', 'public/svg')

// Register browsersync
mix.browserSync('127.13.37.1')

// Add source maps if not in production
if (!mix.inProduction()) {
  mix.sourceMaps()
}

// Linters
mix.webpackConfig({
  module: {
    rules: gumboLoaders
  },
  plugins: gumboPlugins
})
