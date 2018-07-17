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

const register = {
  jQuery,
  bootstrap,
  GMaps,
  imagesloaded,
  elevatezoom,
  MagnificPopup,
  SimpleTextRotator,
  masonry,
  pikaday,
  scrolltrigger,
  jarallax
}

export default () => {
  for (const scriptName in register) {
    if (register.hasOwnProperty(scriptName)) {
      const script = register[scriptName]
      window[scriptName] = document[scriptName] = script
    }
  }
}
