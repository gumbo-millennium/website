/**
 * Handles effects like Parallax and particles
 *
 * @author Roelof Roos <github@roelof.io>
 */

// Dependencies
import particlesJS from 'particles.js'
import jQuery from 'jquery'
import 'jarallax'

// Config
import config from './config'

// Add particles to canvases that request it
if (document.querySelector('#particles-js') !== null) {
  particlesJS('particles-js', config.particles)
}

// Only run if Jarallax is available
if (jQuery.fn.jarallax) {
  jQuery('.parallax').jarallax({ speed: 0.4 })
}

// Fade in page after it's done loading
const fadeInPage = () => {
  console.log('Loaded')

  let layout = document.querySelector('.layout')
  if (layout) {
    layout.classList.add('fade-in')
  }
}

document.addEventListener('load', fadeInPage)
window.addEventListener('load', fadeInPage)
