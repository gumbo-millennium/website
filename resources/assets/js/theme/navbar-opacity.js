/**
 * Handles navbar colour and opacity when scrolling and on page load
 *
 * @author Roelof Roos <github@roelof.io>
 */

import throttle from 'lodash.throttle'

/**
 * Page header
 */
const header = document.querySelector('.header')

/**
 * Header module
 */
const moduleHeader = document.querySelector('.module-header');

/**
 * True if the page contains a fancy title
 */
const hasFancyTitle = moduleHeader !== null || document.querySelector('.module-slides, .flexslider') !== null;

console.log({
  header,
  moduleHeader,
  hasFancyTitle
});

/**
 * Make the navbar opaque and with a shadow when scrolling
 */
const handleNavbarScroll = () => {
    let hasScroll = (window.scrollY || window.pageYOffset) >= 20;

    header.classList.toggle('header-shadow', hasScroll)
    header.classList.toggle('header-small', hasScroll || !hasFancyTitle)
}

// Bind to window
window.addEventListener('scroll', throttle(handleNavbarScroll, 100))

// Fire now
handleNavbarScroll()

// Make header non-translucent
if (!hasFancyTitle) {
  header.classList.add('header-small')
}

// Make header light if the fancy header is dark
if (moduleHeader !== null) {
  header.classList.toggle(
    'header-light',
    moduleHeader.classList.contains('bg-dark')
  )
}
