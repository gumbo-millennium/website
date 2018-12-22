const mix = require('laravel-mix')
const glob = require('glob')
const StyleLintPlugin = require('stylelint-webpack-plugin')
const PurgecssPlugin = require('purgecss-webpack-plugin')

// Make sure we version stuff
mix.version()

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
          cache: true
        }
      }
    ]
  },
  plugins: [
    // new webpack.DefinePlugin({
    //   jquery: ['jQuery']
    // }),
    new StyleLintPlugin({
      files: [
        'resources/assets/sass/**/*.s?(a|c)ss'
      ]
    }),
    new PurgecssPlugin({
      paths: () => [].concat(
        glob.sync(`${__dirname}/resources/views/*.blade.php`),
        glob.sync(`${__dirname}/resources/views/**/*.blade.php`)
      )
    })
  ]
})
