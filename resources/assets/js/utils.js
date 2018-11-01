/**
 * Utilities
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

const csrfHeaderName = 'X-CSRF-TOKEN'

/**
 * Returns the CSRF token
 *
 * @returns {String|null}
 */
const getCsrfToken = () => {
  let tokenMeta = document.querySelector('meta[name="laravel-csrf-token"]')
  return tokenMeta ? tokenMeta.content : null
}

export {
  csrfHeaderName,
  getCsrfToken
}
