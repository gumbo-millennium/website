/**
* Navbar
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

import { default as Utils } from './utils'
import jQuery from 'jquery'
import throttle from 'lodash.throttle'

export default () => {
  let navbar = jQuery('.navbar')

  if (!Utils.UserAgent.isMobile()) {
    dropdownHover()
    transparentFixed(navbar)
  }

  // prevent dropdown link click to hide dropdown
  jQuery('.navbar-nav .dropdown-item').on('click', event => {
    event.stopPropagation()
  })

  // toggle for dropdown submenus
  jQuery('.dropdown-submenu .dropdown-toggle').click(event => {
    event.preventDefault()
    let element = jQuery(this)
    element.parent().toggleClass('show')
    element.siblings('.dropdown-menu').toggleClass('show')
  })

  // Handle sticking the navbar to the bottom of the screen
  fixedBottom(navbar)

  // offcanvas collapsable
  jQuery('[data-toggle="offcanvas"]').on('click', () => {
    jQuery('.offcanvas-collapse').toggleClass('open')
  })
}

/**
 * Expand dropdowns on hover
 */
const dropdownHover = () => {
  document.querySelectorAll('.navbar-nav .dropdown').forEach(node => {
    node.addEventListener('mouseenter', () => {
      node.classList.add('show')
    })
    node.addEventListener('mouseleave', () => {
      node.classList.remove('show')
    })
  })
}

const transparentFixed = navbar => {
  if (!navbar.hasClass('bg-transparent') || !navbar.hasClass('fixed-top')) {
    return
  }

  let navbarTop = navbar.offset().top + 1

  let scrollingFn = function () {
    let offsetTop = window.scrollY || window.pageYOffset

    if (offsetTop >= navbarTop && navbar.hasClass('bg-transparent')) {
      navbar.removeClass('bg-transparent')
    } else if (offsetTop < navbarTop && !navbar.hasClass('bg-transparent')) {
      navbar.addClass('bg-transparent')
    }
  }

  window.addEventListener('scroll', throttle(scrollingFn, 200))
}

const fixedBottom = navbar => {
  if (!navbar.hasClass('navbar-fixed-bottom')) {
    return
  }

  let navbarTop = navbar.offset().top + 1

  let scrollingFn = function () {
    let offsetTop = window.scrollY || window.pageYOffset

    if (offsetTop >= navbarTop && !navbar.hasClass('navbar-fixed-bottom--stick')) {
      navbar.addClass('navbar-fixed-bottom--stick')
    } else if (offsetTop < navbarTop && navbar.hasClass('navbar-fixed-bottom--stick')) {
      navbar.removeClass('navbar-fixed-bottom--stick')
    }
  }

  window.addEventListener('scroll', throttle(scrollingFn, 200))
}
