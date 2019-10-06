/**
 * A library extension
 *
 * @author Roelof Roos
 */
class Library {
  constructor (options) {
    this._options = Object.assign(
      {
        plugins: [],
        loaders: null,
        dependencies: []
      },
      options
    )
  }

  get dependencies () {
    return this._options.dependencies
  }

  get loaders () {
    if (typeof this._options.loaders === 'function') {
      return this._options.loaders()
    }
    return this._options.loaders
  }

  get plugins () {
    if (typeof this._options.plugins === 'function') {
      return this._options.plugins()
    }
    return this._options.plugins
  }
}

module.exports = Library
