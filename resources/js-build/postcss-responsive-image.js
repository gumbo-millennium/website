const _ = require('lodash')
const path = require('path')
const postcss = require('postcss')
const tailwindResolve = require('tailwindcss/resolveConfig')

/**
 * @param {String} url
 * @param {object} size
 * @return {postcss.Decl[]}
 */
const createDeclarations = (url, size) => ([
  // Add standard image
  postcss.decl({
    prop: 'background-image',
    value: `url('${url}?size=${size.normal}')`
  }),
  // Add high-res image via image-set
  postcss.decl({
    prop: 'background-image',
    value: `image-set(url('${url}?size=${size.normal}') 1x, url('${url}?size=${size.double}') 2x)`
  })
])

// Bind plugin
module.exports = postcss.plugin('esetup-responsive', opts => {
  // Get config
  const tailwindConfig = tailwindResolve(require(path.resolve(process.cwd(), 'tailwind.config.js')))
  const screens = _.sortBy(
    _.get(tailwindConfig, 'theme.screens'),
    value => Number.parseInt(value)
  )

  // Calculate sizes
  const imageSizes = _.map(screens, (size, index, arr) => ({
    media: arr[index - 1] || null,
    normal: Number.parseInt(size),
    double: Number.parseInt(size) * 2
  }))

  // Get smallest size
  const defaultRes = _.first(imageSizes)

  return css => {
    // Load options
    opts = opts || {}

    // Iterate background-images
    css.walkDecls('background-image', decl => {
      // Skip if not 'responsive()'
      if (!decl.value || decl.value.indexOf('responsive') === -1) {
        return
      }

      // Get container
      /** @var {postcss.Rule} rule */
      const rule = decl.parent
      const imagePath = decl.value.replace(/responsive\((.+?)\)/, '$1').replace(/^["']|["']$/g, '')

      // Add smallest image on default (mobile-first)
      decl.replaceWith(createDeclarations(imagePath, defaultRes))

      // Always insert at the end
      let insertAfter = rule

      // Add media query afterwards
      imageSizes.forEach(size => {
        // Skip without media
        if (!size.media) {
          return
        }

        // Create @media rule and clone of rule
        const newMedia = postcss.atRule({ name: `media (min-width: ${size.media})` })
        const newRule = postcss.rule({ selectors: rule.selectors })

        // Add items
        insertAfter.after(newMedia)
        newMedia.append(newRule)
        newRule.append(createDeclarations(imagePath, size))

        // Move next cursor
        insertAfter = newMedia
      })
    })
  }
})
