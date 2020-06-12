/**
 * Main application javascript
 */

// Load Vue
import Vue from 'vue'

// Load Axios and Components
import bindComponents from './components'

import initModules from './modules'

// Start app
document.addEventListener('DOMContentLoaded', initModules, { passive: true, once: true })

// Register components
bindComponents()

// Build registration function
const initElement = (selector, element) => {
  // Find Vue-enabled container
  const container = document.querySelector(`[data-content=${selector}]`)

  // Handle content
  if (!container) {
    return
  }

  const props = {}
  for (const key in container.dataset) {
    if (key === 'tinker') {
      continue
    }

    props[key] = container.dataset[key]
  }

  // eslint-disable-next-line no-new
  new Vue({
    el: container,
    render: createElement => createElement(element, { props }, [])
  })
}

// Wait for DOM ready
document.addEventListener('DOMContentLoaded', () => {
  initElement('vue', 'gumbo')
  initElement('bot-tinker', 'botman-tinker')
})
