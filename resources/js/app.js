/**
 * Main application javascript
 */

import initModules from './modules'
import Alpine from 'alpinejs'

// Start app
document.addEventListener('DOMContentLoaded', () => {
  initModules()
})

// Load alpine
Alpine.start()

// Make Vite aware of required images
import.meta.glob([
  '../assets/images/**/*.{png,jpg,webp,svg}',
  '../assets/images-mail/**/*.png',
  '../assets/icons/**/*.{png,svg}',
])
