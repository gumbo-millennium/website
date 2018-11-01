/**
 * Theme bootstrapper
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

import animation from './theme/animation'
import navbar from './theme/navbar'
import brokenImage from './theme/broken-image'

// Load Bootstrap
import 'bootstrap'

// Load early handlers
brokenImage()
navbar()

// Load the rest
animation()
