/**
* Animation class
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

import { jQuery as $ } from 'jquery'

let renderPending = false
const elements = document.querySelectorAll('[data-animate]')

const scrollConfig = {
  capture: false,
  passive: true
}

/**
 * Handles throttling the scroll event until the next animation frame
 */
const scrollHandler = () => {
  if (renderPending) {
    return
  }

  // Request a frame, and block requesting more frames
  renderPending = true
  requestAnimationFrame(update)
}

/**
* Check each element for visibility
*/
const update = () => {
  // Update elements
  elements.forEach((element, index, list) => {
    if (!isInViewport(element)) {
      return
    }

    // Start animating
    triggerAnimate(element)

    // Remove node from list
    elements.filter(node => node !== element)
  })

  // Allow the next frame to render
  renderPending = false
}

/**
* Checks if element is in viewport
*
* @param {HTMLElement} element
* @returns {Boolean} true if visible
*/
const isInViewport = (element) => {
  let elementOffset = $(element).offset()
  let elementTop = elementOffset.top
  let elementBottom = elementOffset.top + element.innerHeight

  let screenTop = window.pageYOffset
  let screenBottom = screenTop + window.innerHeight

  return (screenBottom > elementTop) && (screenTop < elementBottom)
}

/**
* Starts animations on the given element
*
* @param {HTMLElement} element
* @returns {void}
*/
const triggerAnimate = (element) => {
  let effect = element.dataset.animate
  let infinite = element.dataset.animateInfinite || null
  let delay = element.dataset.animateDelay || null
  let duration = element.dataset.animateDuration || null

  if (infinite !== null) {
    element.classList.add('infinite')
  }

  if (delay !== null) {
    element.style.animationDelay = `${delay}s`
  }

  if (duration !== null) {
    element.style.animationDuration = `${duration}s`
  }

  element.classList.add(`animated ${effect}`)
  element.addEventListener('animationend', () => {
    element.classList.add('animation-end')
  }, { once: true })
}

/**
* Called on DOMReady
*/
export default () => {
  // Add scroll listner
  window.addEventListener('scroll', scrollHandler, scrollConfig)

  // Update
  update()
}
