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

])

// Bind plugin
module.exports = (opts = {}) => {
  return {
    postcssPlugin: 'responsive-image',

    prepare (result) {
      const tailwindConfig = tailwindResolve(require(path.resolve(process.cwd(), 'tailwind.config.js')))
      const screens = _.sortBy(
        _.get(tailwindConfig, 'theme.screens'),
        value => Number.parseInt(value)
      )

      const imageSizes = _.map(screens, (size, index, arr) => ({
        media: arr[index - 1] || null,
        normal: Number.parseInt(size),
        double: Number.parseInt(size) * 2
      }))

      // Get smallest size
      const defaultRes = _.first(imageSizes)

      return {
        Declaration (node, { decl }) {
          // Skip if not 'responsive()'
          if (!node.value || node.value.indexOf('responsive') === -1) {
            return
          }

          // Get container
          /** @var {postcss.Rule} rule */
          const rule = node.parent
          const imagePath = node.value.replace(/responsive\((.+?)\)/, '$1').replace(/^["']|["']$/g, '')

          // Add smallest image on default (mobile-first)

          node.replaceWith([
            // Add standard image
            decl({
              prop: 'background-image',
              value: `url('${imagePath}?size=${defaultRes.normal}')`
            }),
            // Add high-res image via image-set
            decl({
              prop: 'background-image',
              value: `image-set(url('${imagePath}?size=${defaultRes.normal}') 1x, url('${imagePath}?size=${defaultRes.double}') 2x)`
            })
          ])

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
        }
      }
    }
  }
}

module.exports.postcss = true
