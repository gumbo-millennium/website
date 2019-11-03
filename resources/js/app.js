/**
 * Main application javascript
 */

// Load Vue
import Vue from 'vue'

// Load Axios and Components
import {default as bindComponents, Components as comps} from './components'

// Register components
bindComponents()

// Register app
const apps = new Set()

// Add apps to window if we're developing
if (process.env.NODE_ENV === 'development') {
  // Prep a debug set on the window
  window.gumbo = {
    apps,
    components: comps
  }

  // Make sure it's frozen
  Object.freeze(window.gumbo)
}

// Wait for DOM ready
document.addEventListener('DOMContentLoaded', () => {
  // Find all activity nodes
  document.querySelectorAll('[data-content=activity]').forEach(node => {
    // Find JSON data in activity
    const jsonNode = node.querySelector('script[type="application/json"]')

    // Parse JSON if present
    const jsonData = jsonNode ? JSON.parse(jsonNode.innerHTML) : {}

    // Create node and add to set
    apps.add(new Vue({
      el: node,
      data: jsonData
    }))
  })
})
