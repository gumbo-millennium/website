/**
 * Handles registering all components
 */

import Vue from 'vue'
import FontAwesome from './fontawesome'
import GumboIcon from './components/shared/icon.vue'
import { TinkerComponent } from 'botman-tinker'

/**
 * Creates all components in Vue
 */
export default function () {
  Vue.component('fa-icon', FontAwesome)
  Vue.component('gumbo-icon', GumboIcon)
  Vue.component('botman-tinker', TinkerComponent)
}
