/**
 * Function to retreive CSS variables from the DOM. Used to get Bootstrap variables and colours.
 *
 * @author Roelof Roos <github@roelof.io>
 */

// Get body style
const bodyStyle = window.getComputedStyle(document.body)

// Prepared regexes
const testRegex = /^(?:0|[\d.]+\s?[a-z]{2, 5})$/
const matchRegex = /^(\d+|\d+\.\d+|\.\d+)\s?([a-z]{2, 5})/

/**
 * Returns the value of the given variable. Or null.
 *
 * @param  {String} name
 * @returns {String|null}
 */
export const getVariable = (name) => {
  let value = bodyStyle.getComputedStyle(`--${name}`).trim()
  return value === '' ? null : value
}

/**
 * Gets a unit from the variables, returning NULL if not valid.
 *
 * @param {String} name Variable to retrieve
 * @param {String} unit Expected unit [default: px]
 * @returns {null|Number}
 */
export const getUnit = (name, unit = 'px') => {
  let variable = getVariable(name)

  // Check if valid variable
  if (!testRegex.test(variable)) {
    return null
  }

  // Return zero if the variable is zero
  if (variable === '0') {
    return 0
  }

  // Get value and type
  let matches = variable.match(matchRegex)
  let varValue = parseFloat(matches[1])
  let varUnit = matches[2].toLowerCase()

  // Returns the value if the variable unit matches the requested unit
  return (varUnit !== unit) ? null : varValue
}

/**
 * Export regexes
 */
export const regexes = {
  test: testRegex,
  match: matchRegex
}
