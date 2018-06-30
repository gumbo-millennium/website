const mix = require('laravel-mix')

// Configure javascript, with separate vendor
mix
  .js('lib/js/plugin.js', 'dist/gumbo-plugin.js')

// Configure SCSS, also with separate vendor (bootstrap)
mix
  .sass('lib/sass/plugin.scss', 'dist/gumbo-plugin.css')

// Add source maps if not in production
if (!mix.inProduction()) {
  mix.sourceMaps()
}

// Linters
mix.webpackConfig({
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'eslint-loader',
        options: {
          cache: true,
          failOnError: true,
          failOnWarning: true
        }
      }
    ]
  }
})
