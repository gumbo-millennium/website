/**
* Navbar
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

import jQuery from 'jquery'
import throttle from 'lodash.throttle'

const passiveEvent = {
  passive: true,
  capture: false
}

const template = '<a class="dropdown-item" href="#"></a>'

/**
 * Validates if the given navbar should have transparency, and removes it if not
 *
 * @param {jQuery} navbar
 */
const validateTransparentNavbar = navbar => {
  // Keep track if we've seen the navbar
  let seenNavbar = false

  // Find the next item, by converting the navbar's parent's children to an array
  // and removing invisibile elements
  let nextNodes = Array.from(navbar.get(0).parentNode.children)
    .filter(node => {
      // If the navbar was seen, allow if not .sr-only
      if (seenNavbar) {
        return !node.classList.contains('sr-only')
      }

      // Otherwise check if it's the navbar, but remove the node anyway
      seenNavbar = node.classList.contains('navbar')
      return false
    })

  // Get first node
  let nextNode = nextNodes.shift()

  // If there's no next node, or the next node is NOT a hero, remove navbar transparency
  if (!nextNode || !nextNode.classList.contains('hero')) {
    let _nav = navbar.get(0)
    _nav.style.transition = 'none'
    _nav.classList.remove('navbar-dark--transparent')
    setTimeout(() => _nav.style.removeProperty('transition'), 100)
  }
}

/**
* Handles the navbar's opacity when scrolling down the page
*
* @param {JQuery} navbar
*/
const bindTransparantNavbar = navbar => {
  // Don't do anything if the navbar isn't the right kind
  if (!navbar.hasClass('navbar-dark') || !navbar.hasClass('fixed-top')) {
    return
  }

  // Dont't do anything if the navbar is flagged as opaque
  if (navbar.hasClass('navbar--opaque')) {
    return
  }

  // Get current top
  const navbarTop = navbar.height() * 0.75

  // Handle changes
  const handleFunction = () => {
    let offsetTop = window.scrollY || window.pageYOffset
    navbar.toggleClass('navbar-dark--transparent', offsetTop <= navbarTop)
  }

  window.addEventListener('scroll', throttle(handleFunction, 200), passiveEvent)
  handleFunction()
}

/**
* Bind events to expand dropdowns when being hovered
*/
const bindHoverNavigation = navbar => {
  navbar.find('.navbar-nav .dropdown:not(.dropdown-submenu)').each((index, item) => {
    let node = jQuery(item)

    // Add item
    let link = node.find('[data-toggle="dropdown"]').eq(0)
    let list = node.children('.dropdown-menu').eq(0)

    if (!link.data('no-append')) {
      let linkNode = jQuery(template)
        .attr('href', link.attr('href'))
        .text(link.text())
        .addClass('d-lg-n')

      // Insert before first link item
      linkNode.insertBefore(list.children().eq(0))
    }

    // Add node
    node.hover(
      () => {
        if (!node.hasClass('show')) {
          link.click()
        }
      },
      () => {
        if (node.hasClass('show')) {
          link.click()
        }
      }
    )
  })
}

/**
* Constructor
*/
export default () => {
  const navbar = jQuery('.navbar')

  // Bind offcanvas
  jQuery('[data-toggle="offcanvas"]').on('click', () => {
    jQuery('.offcanvas-collapse').toggleClass('open')
  })

  // Bind hover nav
  bindHoverNavigation(navbar)

  // Check navbar transparency
  validateTransparentNavbar(navbar)

  // Bind transparent nav
  bindTransparantNavbar(navbar)
}
