/**
 * Gets CSS variables from the root element
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

let windowStyle = null

/**
 * Retrieves a CSS variable
 *
 * @param {string} property CSS variable to retrieve
 * @param {mixed} fallback Fallback value
 * @returns {String} CSS variable value
 */
const getValue = (property, fallback) => {
  if (windowStyle === null) {
    windowStyle = window.getComputedStyle(document.body)
  }
  let value = windowStyle.getPropertyValue(`--${name}`)
  return value.trim() || fallback || null
}

/**
 * Retireves a CSS pixel value, returns number
 *
 * @param {string} property CSS variable to retrieve
 * @param {mixed} fallback Fallback value
 * @returns {number|null} CSS variable value, numeric
 */
const getNumber = (property, fallback) => {
  let value = getValue(property, null)
  if (value === null) {
    return fallback
  }

  if (/^\d+(\.\d+)?(px)?$/.test(value)) {
    return parseFloat(value.replace(/px$/, ''))
  }
  return fallback
}

export default {
  value: getValue,
  unit: getNumber
}
