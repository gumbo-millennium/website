// Main
const mix = require('laravel-mix')
const path = require('path')
const os = require('os')

// Webpack plugins
const ImageminPlugin = require('imagemin-webpack-plugin').default
const CompressionPlugin = require('compression-webpack-plugin')
const ESLintPlugin = require('eslint-webpack-plugin')

const valetHosts = [
  'dionysus'
]

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
  'resources/assets/images/**/*.{svg,jpg,webp,png}',
  'resources/assets/svg/**/*.svg'
]
mix
  .copy(imageAssets, 'public/images/')
  .version(imageAssets)

/**
 * Browsersync
 */
let domain = 'localhost:13370'
if (valetHosts.includes(os.hostname())) {
  const dir = path.basename(__dirname)
  domain = `${dir}.test`
}

// Set new URL
mix.browserSync({
  proxy: domain,
  ghostMode: false
})

/**
 * Add plugins
 */
const plugins = []

// Minify images
if (mix.inProduction()) {
  plugins.push(new ImageminPlugin({
    test: /\.(png|svg|jpg)$/,
    disable: !mix.inProduction()
  }))
}

// Brotli and Gzip, when in production
if (mix.inProduction()) {
  const compressionConfig = {
    test: /\.(js|css|svg)$/,
    threshold: 1024,
    minRatio: 0.8
  }

  plugins.push(
    new CompressionPlugin({
      ...compressionConfig,
      filename: '[path]/[name][ext].br[query]',
      algorithm: 'brotliCompress',
      compressionOptions: { level: 11 }
    })
  )

  plugins.push(
    new CompressionPlugin({
      ...compressionConfig,
      filename: '[path]/[name][ext].gz[query]'
    })
  )
}

// ESLint validation on build
plugins.push(new ESLintPlugin({
  files: [
    'resources/js/**/*.{js,vue}'
  ]
}))

// Push plugins
mix.webpackConfig({ plugins })
