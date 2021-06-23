// Main
const mix = require('laravel-mix')

// Webpack plugins
const ImageminPlugin = require('imagemin-webpack-plugin').default
const ESLintPlugin = require('eslint-webpack-plugin')

/**
 * Register Javascripts
 */
mix
  .js('resources/js/app.js', 'public')

/**
 * Extract vendor code in node_modules
 */
mix.extract()

/**
 * Register stylesheets
 */
mix
  .postCss('resources/css/app.css', 'public')
  .postCss('resources/css/mail.css', 'public')

/**
 * Enable sourcemaps on dev
 */
mix.sourceMaps(false)

/**
 * Add a version and extract vendor if in production
 */
if (mix.inProduction()) {
  mix.version()
}

/**
 * Image assets
 */
const imageAssets = [
  'resources/assets/images-mail/**/*.{svg,jpg,png}',
  'resources/assets/images/**/*.{svg,jpg,webp,png}'
]
mix
  .copy(imageAssets, 'public/images/')
  .version(imageAssets)

// Push plugins
mix.webpackConfig({
  plugins: [
    // Minify images
    new ImageminPlugin({
      test: /\.(png|svg|jpg)$/,
      disable: !mix.inProduction()
    }),

    // ESLint validation on build
    new ESLintPlugin({
      files: [
        'resources/js/**/*.{js,vue}'
      ]
    })
  ]
})
