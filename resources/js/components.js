/**
 * Handles registering all components
 */

import Vue from 'vue'

/**
 * Available vue components
 */
export const Components = new Set({
  // Register vendor components
  'fa-icon': require('./fontawesome').default,

  // Register shared components
  'gumbo-button': require('./components/shared/button').default,
  'gumbo-icon': require('./components/shared/icon').default,
  'gumbo-alert': require('./components/shared/alert').default,

  // Enroll button
  'enroll-button': require('./components/enroll-button.vue').default
})

/**
 * Creates all components in Vue
 */
export default function () {
  Components.forEach(([name, call]) => {
    Vue.component(name, call)
  })
}
