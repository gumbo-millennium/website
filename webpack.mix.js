// Base frame
const mix = require('laravel-mix')

// Plugins and loaders
const CleanWebpackPlugin = require('clean-webpack-plugin').CleanWebpackPlugin
const HardSourceWebpackPlugin = require('hard-source-webpack-plugin')
const ImageminPlugin = require('imagemin-webpack-plugin').default
const StyleLintPlugin = require('stylelint-webpack-plugin')
const WebpackBrotli = require('brotli-webpack-plugin')
const WebpackStrip = require('strip-loader')

// Inline Plugins
require('laravel-mix-purgecss')

// Plugins for PostCSS
const postCssPlugins = [
  require('postcss-import'),
  require('tailwindcss')
]

// Compile stylesheets
mix.postCss('resources/css/app.css', 'public/css/gumbo-millennium.css', postCssPlugins)

// Compile Javascript
mix.js('resources/js/app.js', 'public/js/gumbo-millennium.js')

// Copy files
mix.copy([
  'resources/assets/images/**/*.{jpg,png,jpeg,svg}',
  'resources/assets/svg/**/*.svg'
], 'public/images')

// Add HardSource
mix.webpackConfig({
  plugins: [
    new HardSourceWebpackPlugin()
  ]
})

// Add cleaning
mix.webpackConfig({
  plugins: [
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: [
        'public/fonts/',
        'public/images/',
        'public/svg/',
        'public/**/*.js',
        'public/**/*.css',
        'public/**/*.json'
      ]
    })
  ]
})

// Add Stylelint
mix.webpackConfig({
  plugins: [
    new StyleLintPlugin({
      files: [
        'resources/css/**/*.css'
      ]
    })
  ]
})

// Add ESLint
mix.webpackConfig({
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.(js|vue)$/,
        exclude: /node_modules/,
        loader: 'eslint-loader',
        options: { cache: true }
      }
    ]
  }
})

// Production code
if (mix.inProduction()) {
  // Enable purgecss
  mix.purgeCss()

  // Add custom hash. There's currently a bug that's why the require is late.
  // See https://github.com/ctf0/laravel-mix-versionhash/issues/29
  require('laravel-mix-versionhash')
  mix.versionHash({
    length: 8,
    copy: true
  })

  // Compress content using Brotli
  mix.webpackConfig({
    plugins: [
      new WebpackBrotli({
        test: /\.(js|css|svg)$/
      })
    ]
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

  // Compress images
  mix.webpackConfig({
    plugins: [
      new ImageminPlugin({
        disable: !mix.inProduction()
      })
    ]
  })
}

// Add BrowserSync
mix.browserSync('127.0.0.1:13370')

// Allow our very strict CSP ruleset with Vue.
if (mix.inProduction()) {
  // Ensure Vue uses the runtime version, since the version bundled with Mix
  // doesn't work.
  mix.webpackConfig({
    resolve: {
      alias: {
        vue$: 'vue/dist/vue.runtime.js'
      }
    }
  })

  // Extract CSS from Vue templates, since we cannot append custom css.
  mix.options({
    extractVueStyles: true
  })
}
