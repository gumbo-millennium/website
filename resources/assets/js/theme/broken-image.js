/**
 * Add an 'img-broken' to images if they fail to load
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

/**
 * Class broken images get
 */
const ERROR_CLASS = 'img-broken'

/**
 * Add class list to element
 *
 * @param {Event} event
 */
const addBrokenClass = (event) => {
  console.log('FOUND BROKEN ON %o.', event)

  if (event.target) {
    event.target.classList.add(ERROR_CLASS)
  }
}

/**
 * Bind images for error event
 */
export default () => {
  document.querySelectorAll('img').forEach(function (img) {
    img.addEventListener('onerror', addBrokenClass, {
      once: true,
      passive: true
    })
  })
}
