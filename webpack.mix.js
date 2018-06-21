const mix = require('laravel-mix');
const SassVars = require('babel-plugin-sass-vars')

// Configure javascript, with separate vendor
mix
  .js('resources/assets/js/app.js', 'public/app.js')
  .extract([
    'jquery',
    'popper.js',
    'bootstrap',
    'owl.carousel'
  ])

// Configure SCSS, also with separate vendor (bootstrap)
mix
  .sass('resources/assets/sass/vendor.scss', 'public/vendor.css')
  .sass('resources/assets/sass/app.scss', 'public/app.css')

// Browsersync, used with 'yarn run watch'
mix.browserSync({
  proxy: "gumbo.localhost",
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

// Add source maps if not in production
if (!mix.inProduction()) {
  mix.sourceMaps();
}

// Linters
// TODO
