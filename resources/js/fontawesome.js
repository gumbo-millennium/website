/**
 * Loads Font Awesome icons and a subset of used icons
 */

// Load library
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// Load icons
import { faCheck } from '@fortawesome/free-solid-svg-icons'
import { faSadTear } from '@fortawesome/free-regular-svg-icons'

// Register solid icons
library.add(
  faCheck
)

// Register regular icons
library.add(
  faSadTear
)

// Export icon
export default FontAwesomeIcon
