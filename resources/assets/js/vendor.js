/**
 * Loads all vendor dependencies
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

const jQuery = require('jquery')
const bootstrap = require('bootstrap')
const GMaps = require('gmaps')
const imagesloaded = require('imagesloaded')
const elevatezoom = require('@zeitiger/elevatezoom')
const MagnificPopup = require('magnific-popup')
const SimpleTextRotator = require('jquery.simple-text-rotator')
const masonry = require('masonry-layout')
const pikaday = require('pikaday')
const scrolltrigger = require('scrolltrigger-classes')
const jarallax = require('jarallax')

export default () => {
  window.jQuery = jQuery
  window.bootstrap = bootstrap
  window.GMaps = GMaps
  window.imagesloaded = imagesloaded
  window.elevatezoom = elevatezoom
  window.MagnificPopup = MagnificPopup
  window.SimpleTextRotator = SimpleTextRotator
  window.masonry = masonry
  window.pikaday = pikaday
  window.scrolltrigger = scrolltrigger
  window.jarallax = jarallax
}
