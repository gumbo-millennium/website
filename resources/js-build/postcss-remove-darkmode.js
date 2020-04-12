const postcss = require('postcss')

/*
  Remove dark modes from CSS, used for e-mail
*/

// Bind plugin
module.exports = postcss.plugin('remove-darkmode', () => {
  return (css) => {
    /** @var {postcss.Container} css */
    // Iterate at-rules
    css.walkAtRules((decl) => {
      // Skip at rules that are not a color scheme filter
      if (decl.params.indexOf('prefers-color-scheme') === -1) {
        return
      }

      // Just remove the declaration
      decl.remove()
    })
  }
})
