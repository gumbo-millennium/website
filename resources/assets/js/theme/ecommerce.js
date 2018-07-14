/**
* e-commerce initialisation
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

export default () => {
  bindSearch()
  bindCart()
}

/**
 * Passive events are lighter on the user's CPU
 */
const passiveEvent = { capture: false, passive: true }

/**
 * Binds the search action to light up when it's focussed
 */
const bindSearch = () => {
  document.querySelectorAll('.store-navbar .search-field').forEach(field => {
    let input = field.querySelector('.input-search')

    input.addEventListener('focus', () => {
      field.classList.add('focus')
    }, passiveEvent)

    input.addEventListener('blur', () => {
      field.classList.remove('focus')
    }, passiveEvent)
  })
}

/**
 * Allows the chart to open when hovered
 */
const bindCart = () => {
  let cart = document.querySelector('.store-navbar .cart')
  let modal = document.querySelector('#cart-modal')

  // Disable if without e-commerce elements
  if (!cart || !modal) {
    return
  }

  let timeout = null

  const showModal = function () {
    modal.addClass('visible')

    clearTimeout(timeout)
    timeout = null
  }

  const hideModal = function () {
    timeout = setTimeout(function () {
      modal.removeClass('visible')
    }, 400)
  }

  cart.addEventListener('mouseenter', showModal, passiveEvent)
  modal.addEventListener('mouseenter', showModal, passiveEvent)

  cart.addEventListener('mouseleave', hideModal, passiveEvent)
  modal.addEventListener('mouseleave', hideModal, passiveEvent)
}
