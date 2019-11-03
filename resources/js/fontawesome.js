/**
 * Loads Font Awesome icons and a subset of used icons
 */

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
  faArrowLeft,
  faCheck,
  faChevronRight,
  faCircleNotch,
  faExclamation,
  faInfo,
  faSpinner,
  faStar,
  faSync,
  faTimesCircle,
  faUser,
  faUserFriends,
  faUsers
} from '@fortawesome/free-solid-svg-icons'
import {
  faSadTear
} from '@fortawesome/free-regular-svg-icons'

// Register solid icons
library.add(
  faArrowLeft,
  faCheck,
  faChevronRight,
  faCircleNotch,
  faExclamation,
  faInfo,
  faSpinner,
  faStar,
  faSync,
  faTimesCircle,
  faUser,
  faUserFriends,
  faUsers
)

// Register regular icons
library.add(
  faSadTear
)

// Export icon
export default FontAwesomeIcon
