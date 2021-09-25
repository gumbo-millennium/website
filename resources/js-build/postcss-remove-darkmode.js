/*
  Remove dark modes from CSS, used for e-mail
*/

// Bind plugin

module.exports = () => {
  return {
    postcssPlugin: 'remove-darkmode',

    AtRule: {
      media: atRule => {
        // Skip at rules that are not a color scheme filter
        if (atRule.params.indexOf('prefers-color-scheme') === -1) {
          return
        }

        // Just remove the declaration
        atRule.remove()
      },
    },
  }
}

module.exports.postcss = true
