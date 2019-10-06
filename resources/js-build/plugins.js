// Fundamental dependencies
const mix = require('laravel-mix')
const path = require('path')
const file = require('fs')
const glob = require('glob')

// Local dependencies
const Library = require('./modules/library')

/**
 * Returns empty set if the options are the 'true' boolean
 *
 * @param {Object|bool|null} options Options as-is or null
 */
const buildOptions = options => (options === true ? null : options)

/**
 * Finds the root most likely to contain the project code (look for node_modules)
 *
 * @param {String} curPath Path to start looking at
 */
const findRoot = (curPath = null) => {
  // Check if there's a node_modules folder here
  if (file.existsSync(path.resolve(curPath, 'node_modules'))) {
    return curPath
  }

  // Prepare to go up
  let parentPath = path.resolve(curPath, '../')

  // If parent path === current path, we're at the filesystem root, which means we
  // couldn't find a proper root
  if (parentPath === curPath) {
    return null
  }

  // If curPath contains node_modules, the parent directory is the root
  if (curPath === 'node_modules') {
    return path.dirname(curPath)
  }

  // Recursively find the node_modules directory
  return findRoot(path.dirname(curPath))
}

/**
 * Root of the project, most likely
 */
const codeRoot = findRoot(__dirname) || process.cwd()

class Plugins {
  constructor () {
    this.libraries = []
    this.libraryNames = []
  }

  register (options = {}) {
    this.options = Object.assign(
      {
        clean: true,
        imagemin: true,
        hardsource: true,
        purgecss: true,
        stylelint: true,
        spritemap: false,
        eslint: true
      },
      options
    )

    if (this.options.clean) {
      this.libraryNames.push('clean')
      this.libraries.push(
        this.getClean(buildOptions(this.options.clean))
      )
    }

    if (this.options.imagemin) {
      this.libraryNames.push('imagemin')
      this.libraries.push(
        this.getImagemin(buildOptions(this.options.imagemin))
      )
    }

    if (this.options.hardsource) {
      this.libraryNames.push('hardsource')
      this.libraries.push(
        this.getHardsource(buildOptions(this.options.hardsource))
      )
    }

    if (this.options.purgecss) {
      this.libraryNames.push('purgecss')
      this.libraries.push(
        this.getPurgecss(buildOptions(this.options.purgecss))
      )
    }

    if (this.options.stylelint) {
      this.libraryNames.push('stylelint')
      this.libraries.push(
        this.getStylelint(buildOptions(this.options.stylelint))
      )
    }

    if (this.options.spritemap) {
      this.libraryNames.push('spritemap')
      this.libraries.push(
        this.getSpritemap(buildOptions(this.options.spritemap))
      )
    }

    if (this.options.eslint) {
      this.libraryNames.push('eslint')
      this.libraries.push(
        this.getStylelint(buildOptions(this.options.eslint))
      )
    }
  }

  dependencies () {
    // Start off empty
    let dependencies = ['path', 'fs', 'glob']

    // Load each item
    this.libraries.forEach((node, index) => {
      if (!(node instanceof Library)) {
        console.warn(`Node ${index} (${this.libraryNames[index]}) is not of type Library, got ${typeof node}`)
        return
      }

      let nodeDeps = node.dependencies
      if (nodeDeps && nodeDeps.length > 0 && nodeDeps instanceof Array) {
        nodeDeps.forEach(item => dependencies.push(item))
      }
    })

    // Return all dependencies
    return dependencies
  }

  webpackConfig (webpackConfig) {
    // Load each item
    this.libraries.forEach(node => {
      // Let the class create plugins and loaders and quietly 'cache' the result
      let plugins = node.plugins
      let loaders = node.loaders

      // Add plugins
      if (plugins) {
        webpackConfig.plugins.push(plugins)
      }

      // Add loaders
      if (loaders) {
        if (Array.isArray(loaders)) {
          loaders.forEach(loader => webpackConfig.module.rules.push(loader))
        } else {
          webpackConfig.module.rules.push(loaders)
        }
      }
    })

    // Return modified config
    return webpackConfig
  }

  getClean (options) {
    // Library object
    return new Library({
      dependencies: ['clean-webpack-plugin'],
      plugins: () => {
        // Load dependencies
        const { CleanWebpackPlugin } = require('clean-webpack-plugin')

        // Set default options
        const defaultOptions = {
          cleanOnceBeforeBuildPatterns: [
            'fonts/',
            'images/',
            'svg/',
            '*.js',
            'css/*.css',
            '*.json'
          ]
        }

        // Create plugin
        return new CleanWebpackPlugin(Object.assign(defaultOptions, options))
      }
    })
  }

  getImagemin (options) {
    // Library object
    return new Library({
      dependencies: ['imagemin-mozjpeg', 'imagemin-webpack-plugin'],
      plugins: () => {
      // Load dependencies
        const imageminMozjpeg = require('imagemin-mozjpeg')
        const ImageminPlugin = require('imagemin-webpack-plugin').default

        // Set default options
        const defaultOptions = {
          disable: !mix.inProduction(),
          jpegtran: null,
          plugins: [
            imageminMozjpeg({
              quality: 95,
              progressive: true
            })
          ]
        }

        // Create plugin
        return new ImageminPlugin(Object.assign(defaultOptions, options))
      }
    })
  }

  getHardsource (options) {
    // Library object
    return new Library({
      dependencies: ['hard-source-webpack-plugin'],
      plugins: () => {
        // Load dependencies
        const HardSourceWebpackPlugin = require('hard-source-webpack-plugin')

        // Create plugin
        return new HardSourceWebpackPlugin(options)
      }
    })
  }

  getPurgecss (options) {
    // Function that creates the actual plugin
    const pluginFunction = () => {
      // Load dependencies
      const PurgecssPlugin = require('purgecss-webpack-plugin')

      // Set default options
      const defaultOptions = {
        paths: glob.sync(`${codeRoot}/resources/views/***/*.blade.php`)
      }

      // Create plugin
      return new PurgecssPlugin(Object.assign(defaultOptions, options))
    }

    // Library object
    const library = new Library({
      dependencies: ['purgecss-webpack-plugin'],
      plugins: pluginFunction
    })

    // Add library
    return library
  }

  getStylelint (options) {
    // Function that creates the actual plugin
    const pluginFunction = () => {
      // Load dependencies
      const StyleLintPlugin = require('stylelint-webpack-plugin')

      // Set default options
      const defaultOptions = {
        files: [
          'resources/{css,sass,scss}/**/*.{scss,css}'
        ]
      }

      // Create plugin
      return new StyleLintPlugin(Object.assign(defaultOptions, options))
    }

    // Library object
    const library = new Library({
      dependencies: ['stylelint', 'stylelint-webpack-plugin'],
      plugins: pluginFunction
    })

    // Add library
    return library
  }

  getSpritemap (options) {
    // Function that creates the actual plugin
    const pluginFunction = () => {
      // Load dependencies
      const SvgSpritemapPlugin = require('svg-spritemap-webpack-plugin')

      // Set default options
      const defaultOptions = {
        input: {
          files: [
            'resources/assets/icons/*.svg',
            'resources/assets/icons/**/*.svg'
          ]
        },
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
      }

      // Build options
      const builtOptions = Object.assign(defaultOptions, options)

      // Extract input files and delete them from the options
      const inputFiles = builtOptions.input.files
      delete builtOptions.input.files

      // Create plugin
      return new SvgSpritemapPlugin(inputFiles, builtOptions)
    }

    // Library object
    const library = new Library({
      dependencies: ['svg-spritemap-webpack-plugin'],
      plugins: pluginFunction
    })

    // Add library
    return library
  }

  getEslint (options) {
    // Set default options
    const defaultOptions = {
      cache: true
    }

    // Create plugin
    const loaders = [].push({
      enforce: 'pre',
      test: /\.js$/,
      exclude: /node_modules/,
      loader: 'eslint-loader',
      options: Object.assign(defaultOptions, options)
    })

    let dependencies = ['eslint', 'eslint-loader']
    if (options.standard === true) {
      dependencies.push(
        'eslint-config-standard',
        'eslint-plugin-import',
        'eslint-plugin-node',
        'eslint-plugin-promise',
        'eslint-plugin-standard'
      )
    }

    // Library object
    const library = new Library({
      dependencies: dependencies,
      loaders: loaders
    })

    // Add library
    return library
  }
}

mix.extend('gumbo', new Plugins())
