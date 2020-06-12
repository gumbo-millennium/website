/**
 * Navbar
 */

/** @var {HTMLInputElement} navbarCheckbox */
let navbarCheckbox = null

/**
 * Checks if the given event was in a navbar
 * @param {Event} event
 */
const wasInNavbar = (event) => {
  // Assertions everywhere
  console.assert(event instanceof Event)

  // Bind target
  let target = event.target
  let limit = 40

  // Traverse as long as it's an element
  while (target instanceof Element && target !== document.body && (--limit) > 0) {
    if (target.classList.contains('navbar')) {
      return true
    }
    target = target.parentNode
  }

  // No match in the loop
  return false
}

/**
 * Close the nav if open and the user clicked outside it
 * @param {Event} event
 */
const maybeCloseNav = (event) => {
  // Don't even start if not open
  if (!navbarCheckbox.checked) {
    return
  }

  // Check parents
  if (wasInNavbar(event)) {
    return
  }

  // Uncheck navbar, hiding it
  navbarCheckbox.checked = false
}

/**
 * Binds the menu toggle
 */
const bindMenuToggle = () => {
  // Find checkbox in navbar
  navbarCheckbox = document.querySelector('.navbar #navbar-toggle')

  // Do nothing if missing
  if (!navbarCheckbox) {
    return
  }

  // Always have it start closed
  navbarCheckbox.checked = false

  // Add click listener
  document.addEventListener('click', maybeCloseNav, { passive: true })
}

const init = () => {
  bindMenuToggle()
}

export default init
