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
