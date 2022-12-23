// svgo.config.js
module.exports = {
  multipass: true, // boolean. false by default
  plugins: [
    {
      name: 'preset-default',
      params: {
        override: {
          removeViewBox: false,
        },
      },
    },

    // Use viewbox instead of width and height
    'removeDimensions',

    // Remove script and style tags
    'removeScriptElement',
    'removeStyleElement',

    // Remove anything on a raster
    'removeRasterImages',
  ],
}
