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
* Handles the navbar's opacity when scrolling down the page
*
* @param {JQuery} navbar
*/
const bindTransparantNavbar = navbar => {
  // Don't do anything if the navbar isn't the right kind
  if (!navbar.hasClass('bg-transparent') || !navbar.hasClass('fixed-top')) {
    return
  }

  // Get current top
  const navbarTop = navbar.offset().top + 15

  // Handle changes
  const handleFunction = () => {
    let offsetTop = window.scrollY || window.pageYOffset
    navbar.toggleClass('bg-transparent', offsetTop <= navbarTop)
  }

  window.addEventListener('scroll', throttle(handleFunction, 200), passiveEvent)
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

    let linkNode = jQuery(template)
      .attr('href', link.attr('href'))
      .text(link.text())
      .addClass('d-lg-n')

    // Insert before first link item
    linkNode.insertBefore(list.children().eq(0))

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

  // Bind transparent nav
  bindTransparantNavbar(navbar)
}
