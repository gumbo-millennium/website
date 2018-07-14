const mix = require('laravel-mix')
const glob = require('glob')
const StyleLintPlugin = require('stylelint-webpack-plugin')
const PurgecssPlugin = require('purgecss-webpack-plugin')

// Configure javascript, with separate vendor
mix
  .js('resources/assets/js/theme.js', 'public/gumbo.js')
  .extract([
    '@zeitiger/elevatezoom',
    'bootstrap',
    'debounce',
    'gmaps',
    'imagesloaded',
    'jarallax',
    'jquery',
    'jquery.simple-text-rotator',
    'lodash.throttle',
    'magnific-popup',
    'masonry-layout',
    'mobile-detect',
    'pikaday',
    'popper.js',
    'zoomerang'
  ])

// Configure SCSS, also with separate vendor (bootstrap)
mix
  .sass('resources/assets/sass/theme.scss', 'public/gumbo.css')

// Browsersync, used with 'yarn run watch'
mix.browserSync({
  proxy: 'gumbo.localhost',
  files: [
    './resources/assets/js/**/*.js',
    './resources/assets/sass/**/*.scss'
  ]
})

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
