/**
 * Handles the mega menu.
 *
 * @version 1.2
 * @author web-master72
 * @author Roelof Roos <github@roelof.io>
 */
import $ from 'jquery'
import debounce from 'debounce'
import detectIt from 'detect-it'

/**
 * "lg" breakpoint in Bootstrap
 */
const breakpointLg = 992
const openClass = 'sub-menu-open'
const menuQuery = '.menu-item-has-children:not(.mega-menu-col)'

/**
 * Navbar to apply changes to
 */
const navBar = document.querySelectorAll(menuQuery)
const navBarLinks = document.querySelectorAll(`${menuQuery} > a`)
const navBarMenus = document.querySelectorAll(`${menuQuery} > .sub-menu, ${menuQuery} > .mega-menu`)

/**
 * Closes all menus and their submenus
 * @param {DOMNode} exception Menu NOT to close, or null
 */
const closeMenus = (exception = null) => {
  navBar.forEach(node => {
    if (!exception || node !== exception) {
      node.classList.remove(openClass)
      node.querySelectorAll(openClass).forEach(node => node.classList.remove(openClass))
    }
  })
}

/**
 * Opens the menu that's being hovered
 * @param {Event} event DOM event (usually a MouseEvent)
 */
const mouseEnterSubMenu = (event) => {
  console.log('Entered submenu %o', event.currentTarget)
  event.currentTarget.classList.add(openClass)
}

/**
 * Closes the menu that's no longer being hovered
 * @param {Event} event DOM event (usually a MouseEvent)
 */
const mouseLeaveSubMenu = (event) => {
  console.log('Left submenu %o', event.currentTarget)
  event.currentTarget.classList.remove(openClass)
}

/**
 * Handles clicking menu items with submenus. Preventing the regular link from functioning
 *
 * @param {Event} event DOM event (usually a MouseEvent)
 */
const mouseClickSubMenu = (event) => {
  // Prevent the click
  event.preventDefault()

  // Get target and parent menu
  let menu = event.currentTarget.parentNode

  // Check if the menu is currently open
  let wasOpen = menu.classList.contains(openClass)

  // Close all open menus, except for the current one
  closeMenus(menu)

  // Open or close this menu, depending on it's previous state
  menu.classList.toggle(openClass, !wasOpen)

  // Also close all child menus, if the menu is closing
  if (wasOpen) {
    menu.querySelectorAll(openClass).forEach(node => node.classList.remove(openClass))
  }
}

/**
 * Unbinds click and hover events
 */
const unbindListeners = () => {
  // Unbind navbar
  navBar.forEach(node => {
    node.removeEventListener('mouseenter', mouseEnterSubMenu)
    node.removeEventListener('mouseleave', mouseLeaveSubMenu)
  })

  // Unbind links
  navBarLinks.forEach(node => node.removeEventListener('click', mouseClickSubMenu))
}

/**
 * Binds the navbar to open menus on hover
 */
const bindHoverEvents = () => {
  navBar.forEach(node => {
    // Add passive listeners, since they're not blocking anything
    node.addEventListener('mouseenter', mouseEnterSubMenu, { passive: true })
    node.addEventListener('mouseleave', mouseLeaveSubMenu, { passive: true })
  })
}

/**
 * Binds the navbar's <A> tags to toggle the submenus when clicked
 */
const bindClickEvents = () => {
  // Add click listeners. They block the <A> tag from working
  navBarLinks.forEach(node => node.addEventListener('click', mouseClickSubMenu))
}

/**
 * Binds click or hover events, depending on screen width and mouse support
 */
const rebindEvents = () => {
  let windowWidth = window.innerWidth

  // Unbind everything
  unbindListeners()

  // Re-bind events, depending on the screen size and if the user has a mouse
  if (!detectIt.hasMouse || windowWidth < breakpointLg) {
    // Bind click events
    bindClickEvents()
  } else {
    // Bind events to open on hover
    bindHoverEvents()
    // Also close menus
    closeMenus(null)
  }
}

/**
 * Handles aligning menu's, making them always be within view
 */
const refitMenus = () => {
  let width = window.innerWidth

  // Reset margins on menus
  navBarMenus.forEach((node) => {
    node.style.marginLeft = 0
  })

  // Fix position of the mega menu's
  // If not A and B
  if (width > breakpointLg && detectIt.hasMouse) {
    navBarMenus.forEach(domNode => {
      let node = $(domNode)
      let offset = node.offset()
      let position = node.width() + offset.left
      let target = width - (position + 30)

      node.css({
        marginLeft: position > width ? target : 0
      })
    })
  }
}

// Debounce the resize event, causing it to only fire AFTER the resize action has completed
window.addEventListener('resize', debounce(() => {
  console.log('Find')

  refitMenus()
  rebindEvents()
}, 100), { passive: true })

/**
 * Adds a link to the menu, allowing the root element to be clicked
 */
const addMobileLinks = () => {
  navBar.forEach(node => {
    let link = node.firstChild
    let menu = node.querySelector('.sub-menu, .mega-menu')

    // If we don't have a menu, abort
    if (!menu) {
      return
    }

    // Check first anchor in menu, abort if the link already exists
    let firstAnchor = menu.querySelector('li:first-child > a')
    if (firstAnchor.href === link.href) {
      return
    }

    // Create containing list item
    let linkListItem = document.createElement('li')

    // Create anchor, add href from link and hide on breakpoint 'lg' and up
    let linkAnchor = document.createElement('a')
    linkAnchor.setAttribute('href', link.href)
    linkAnchor.classList.add('d-lg-none')

    // Merge everything
    linkListItem.appendChild(linkAnchor)
    menu.insertBefore(linkListItem, menu.firstChild)
  })
}

// Bind events too
rebindEvents()
refitMenus()

// And add the extra links
addMobileLinks()
