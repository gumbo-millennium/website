/**
 * Registers a bunch of plugins in an array, which is appended to Laravel Mix
 */
const mix = require('laravel-mix')
const HardSourceWebpackPlugin = require('hard-source-webpack-plugin')
const ImageminPlugin = require('imagemin-webpack-plugin').default
const imageminMozjpeg = require('imagemin-mozjpeg')
const StyleLintPlugin = require('stylelint-webpack-plugin')
const SvgSpritemapPlugin = require('svg-spritemap-webpack-plugin')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')

/**
 * Configuration of plugins for Webpack
 */

const loaders = []
const plugins = []

/**
 * Always use caching and cleaning plugins
 */
plugins.push(new HardSourceWebpackPlugin())
plugins.push(new CleanWebpackPlugin({
  cleanOnceBeforeBuildPatterns: [
    'fonts/',
    'images/',
    'svg/',
    '*.js',
    'css/*.css',
    '*.json'
  ]
}))

/**
 * Load ESLint
 */
loaders.push({
  enforce: 'pre',
  test: /\.js$/,
  exclude: /node_modules/,
  loader: 'eslint-loader',
  options: {
    cache: true
  }
})

/**
 * Configure stylelint
 */
plugins.push(new StyleLintPlugin({
  files: [
    'resources/assets/sass/**/*.s?(a|c)ss'
  ]
}))

/**
 * Configure imagemin
 */
plugins.push(new ImageminPlugin({
  disable: !mix.inProduction(),
  jpegtran: null,
  plugins: [
    imageminMozjpeg({
      quality: 95,
      progressive: true
    })
  ]
}))

/**
 * Configure SVG spritemap
 *
 * IMPORTANT: Must load AFTER imagemin plugin
 */
plugins.push(new SvgSpritemapPlugin([
  'resources/assets/icons/*.svg',
  'resources/assets/icons/**/*.svg'
], {
  output: {
    filename: 'images/iconmap.svg',
    svgo: true
  },
  sprite: {
    prefix: 'icon-',
    generate: {
      title: true,
      symbol: '-sym',
      use: true,
      view: true
    }
  }
}))

module.exports = { loaders, plugins }
