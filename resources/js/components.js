/**
 * Handles registering all components
 */

import Vue from 'vue'

/**
 * Available vue components
 */
export const Components = new Set({
  // Register icon stuff
  'fa-icon': require('./fontawesome').default,
  'gumbo-icon': require('./components/shared/icon.vue').default
})

/**
 * Creates all components in Vue
 */
export default function () {
  Components.forEach(([name, call]) => {
    Vue.component(name, call)
  })
}
