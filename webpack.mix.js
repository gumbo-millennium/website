// Main
const mix = require('laravel-mix')
const path = require('path')

// Webpack plugins
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
mix.sourceMaps(false, 'source-map')

/**
 * Copy assets to public
 */
mix
  .copyDirectory('resources/assets/images', 'public/images')
  .copyDirectory('resources/assets/images-mail', 'public/images-mail')
  .copyDirectory('resources/assets/icons', 'public/icons')

/**
 * Add a version and extract vendor if in production
 */
if (mix.inProduction()) {
  mix.version([
    'images/**/*.{jpg,png,gif,webp}',
    'images-mail/**/*.{jpg,png,gif,webp}',
  ])
}

/**
 * Image assets
 */
const imageAssets = [
  'resources/assets/images-mail/',
  'resources/assets/images/',
]
mix
  .copy(imageAssets, 'public/images/')
  .version(imageAssets)

/**
 * Aliases
 */
mix.alias({
  '@': path.resolve(__dirname, 'resources/js'),
  '@images': path.resolve(__dirname, 'resources/assets/images'),
})

mix.override(webpack => {
  // Allow webpack loaders, except those handling images
  const allowedWebpackLoaders = webpack.module.rules
    .filter(rule => !('test' in rule && rule.test instanceof RegExp && rule.test.test('@images/test.jpg')))

  // Push the responsive-loader
  allowedWebpackLoaders.push({
    test: /\.(jpe?g|png|gif|webp)$/,
    loader: 'responsive-loader',
    options: {
      // Save using the original filename, but add a hash for cache-busting
      name: '[name]-[width].[hash:6].[ext]',

      // Default quality is 85, which is too low
      quality: 90,

      // Use sharp, since we're using webp
      adapter: require('responsive-loader/sharp'),
    },
  })

  // Override the ruleset
  webpack.module.rules = allowedWebpackLoaders

  // Done
  return webpack
})

// Push plugins
mix.webpackConfig({
  plugins: [
    // ESLint validation on build
    new ESLintPlugin({
      files: [
        'resources/js/**/*.{js,vue}',
      ],
    }),
  ],
})

// Stop repeat success notifications
mix.disableSuccessNotifications()
