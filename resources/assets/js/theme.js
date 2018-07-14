/**
 * Theme bootstrapper
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

import animation from './theme/animation'
import ecommerce from './theme/ecommerce'
import globalNotices from './theme/global-notices'
import navbar from './theme/navbar'
import offcanvas from './theme/offcanvas'
import pricingCharts from './theme/pricing-charts'
import retina from './theme/retina'
import vendor from './vendor'
import zoomerang from './theme/zoomerang'

// Load dependencies
vendor()

// Load the rest
animation()
ecommerce()
globalNotices()
navbar()
offcanvas()
pricingCharts()
retina()
zoomerang()
