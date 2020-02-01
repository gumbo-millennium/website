// Essentials
const path = require('path')
const fs = require('fs')

// Plugins
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const { VueLoaderPlugin } = require('vue-loader')
const CompressionPlugin = require('compression-webpack-plugin')
const CopyPlugin = require('copy-webpack-plugin')
const HardSourcePlugin = require('hard-source-webpack-plugin')
const ImageminPlugin = require('imagemin-webpack-plugin').default
const ManifestPlugin = require('webpack-manifest-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const BrowserSyncPlugin = require('browser-sync-webpack-plugin')
const WebpackStrip = require('strip-loader')

// Locally used variables
const inProduction = process.env.NODE_ENV === 'production'

// Add hash if in production
const withHash = name => (inProduction ? name.replace(/\[(id|name)\]/, '[$1].[contenthash:16]') : name)

// Naming
const publicDir = path.resolve(__dirname, 'public')
const hotFile = path.resolve(publicDir, 'hot')

// SVGO config (compat with mPDF)
const imageMinPlugins = !inProduction ? [] : [
  require('imagemin-gifsicle')({}),
  require('imagemin-mozjpeg')({}),
  require('imagemin-optipng')({}),
  require('imagemin-svgo')({
    plugins: [
      // Fix for PDF parser not understanding this.
      { convertPathData: { noSpaceAfterFlags: false } }
    ]
  })
]

// Compression config
const compressionConfig = {
  test: inProduction ? /\.(js|css|svg)$/ : /^-$/, // Match nothing in testing
  threshold: 1024,
  minRatio: 0.8
}

// Add filter function
const filter = arr => arr.filter(value => value !== null)

try {
  // Remove "hot" file.
  fs.unlinkSync(hotFile)
} catch (err) {
  // Ignore error
}

module.exports = {
  // Set mode and source maps
  mode: inProduction ? 'production' : 'development',

  // Development config
  devtool: inProduction ? false : 'source-map',

  // Configure devserver as a transparent proxy
  devServer: {
    index: '', // specify to enable root proxying
    host: '127.0.0.1',
    port: 3100,
    contentBase: publicDir,
    writeToDisk: true,
    proxy: {
      context: () => true,
      changeOrigin: true,
      target: 'http://127.0.0.1:13370',
      onProxyReq (proxyReq) {
        proxyReq.removeHeader('If-Modified-Since')
      },
      onProxyRes (proxyRes) {
        proxyRes.headers['cache-control'] = 'no-cache, must-revalidate'
        proxyRes.headers.expires = 'Tue, 1 Jan 2019 00:00:00 GMT'
        delete proxyRes.headers.etag
        delete proxyRes.headers.date
      }
    }
  },

  // Context and entry file
  context: __dirname,
  entry: {
    app: ['./resources/js/app.js', './resources/css/app.css'],
    mail: ['./resources/css/mail.css']
  },

  // Output
  output: {
    filename: withHash('[name].js'),
    chunkFilename: withHash('[name].js'),
    path: publicDir,
    publicPath: '/'
  },

  // Various optimizations
  optimization: {
    // Enable tree shaking
    usedExports: true,

    // Use hashes as IDs
    moduleIds: 'hashed',

    // Extract vendors
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendor',
          chunks: 'all'
        }
      }
    }
  },

  // Disable hints. They're useful but I know my JS files are big
  performance: {
    hints: false
  },

  // Loaders
  module: {
    rules: [
      // Linting
      {
        enforce: 'pre',
        test: /\.(js|vue)$/,
        exclude: /node_modules/,
        loader: 'eslint-loader'
      },
      // Stylesheets
      {
        test: /\.css$/,
        use: [
          { loader: MiniCssExtractPlugin.loader, options: { hmr: !inProduction } },
          { loader: 'css-loader', options: { modules: false, importLoaders: 1 } },
          { loader: 'postcss-loader' }
        ]
      },
      // VueJS
      {
        test: /\.vue$/,
        exclude: /node_modules/,
        use: filter([
          'vue-loader',
          inProduction ? WebpackStrip.loader('console.log', 'console.debug', 'console.info') : null
        ])
      },
      // Babel
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: filter([
          'babel-loader',
          inProduction ? WebpackStrip.loader('console.log', 'console.debug', 'console.info') : null
        ])
      },
      // Images
      {
        test: /\.(png|jpe?g|gif|svg|webp)$/,
        use: [
          {
            loader: 'url-loader',
            options: {
              // Max file size to inline
              limit: 2048,

              // Fallback options (file-loader)
              fallback: 'file-loader',
              name: withHash('[name].[ext]'),
              outputPath: 'images'
            }
          },
          {
            loader: 'img-loader',
            options: {
              plugins: imageMinPlugins
            }
          }
        ]
      }
    ]
  },

  // Plugins
  plugins: [
    // Hardsource cache
    new HardSourcePlugin(),

    // Output cleaning
    new CleanWebpackPlugin({
      cleanOnceBeforeBuildPatterns: [
        // Remove compressed files
        './**/*.{br,gz}',

        // remove old Javascript and CSS code
        './*.{css,js}',
        './js/*.js',
        './css/*.css',

        // remove old js, css and image files
        './{images,assets,svg}/**/*'
      ]
    }),

    // Vue compiler
    new VueLoaderPlugin(),

    // CSS extractor
    new MiniCssExtractPlugin({
      filename: withHash('[name].css'),
      chunkFilename: withHash('[id].css')
    }),

    // Copy images
    new CopyPlugin([
      {
        test: /\.(png|svg|jpg)$/,
        from: 'resources/assets/{images,svg}/**/*',
        to: withHash('images/[name].[ext]'),
        toType: 'template',
        writeToDisk: true
      }
    ]),

    // Minify images
    new ImageminPlugin({
      disable: !inProduction,
      plugins: imageMinPlugins
    }),

    // Brotli compression
    new CompressionPlugin({
      ...compressionConfig,
      filename: '[path].br[query]',
      algorithm: 'brotliCompress',
      compressionOptions: { level: 11 }
    }),

    // GZip compression
    new CompressionPlugin({
      ...compressionConfig,
      filename: '[path].gz[query]'
    }),

    // Webpack Manifest
    new ManifestPlugin({
      fileName: 'mix-manifest.json',
      basePath: '/',
      filter: (file) => !(file.name.match(/\.(gz|br)$/) || file.isModuleAsset),
      map: (file) => {
        // Remove hash in manifest key, if present
        if (inProduction) {
          file.name = file.name.replace(/(\.[a-f0-9]{16})(\..+)$/, '$2')
        }

        // Return new file
        return file
      }
    }),

    // BrowserSync
    new BrowserSyncPlugin(
      // BrowserSync options
      {
        open: 'external',
        port: 3000,
        proxy: 'http://localhost:3100/',
        callbacks: {
          // eslint-disable-next-line handle-callback-err
          ready: function (err, bs) {
            /** @var {Map} urls */
            const urls = bs.options.get('urls')
            if (urls && urls.has('external')) {
              fs.writeFileSync(hotFile, urls.get('external'))
              return
            }
            console.error('Failed to write hotfile, Laravel won\'t know about HMR')
          }
        }
      },
      // Starter options
      {
        reload: false,
      }
    )

  ],

  // Aliasses
  resolve: {
    alias: Object.assign({
      images: `${__dirname}/resources/assets/images`
    }, !inProduction ? {} : {
      vue$: 'vue/dist/vue.runtime.js'
    })
  }
}
