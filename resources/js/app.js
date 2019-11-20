/**
 * Main application javascript
 */

// Load Vue
import Vue from 'vue'

// Load Axios and Components
import bindComponents from './components'

// Register components
bindComponents()

// Wait for DOM ready
document.addEventListener('DOMContentLoaded', () => {
  // Find Vue-enabled container
  const container = document.querySelector('[data-content=vue]')

  // Handle content
  if (container) {
    return new Vue({
      el: container,
      render: createElement => createElement('gumbo', { props: {} }, [])
    })
  }
})
