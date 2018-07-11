/**
 * Theme bootstrapper
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

import { default as animation } from './theme/animation'
import { default as ecommerce } from './theme/ecommerce'
import { default as globalNotices } from './theme/global-notices'
import { default as navbar } from './theme/navbar'
import { default as offcanvas } from './theme/offcanvas'
import { default as pricingCharts } from './theme/pricing-charts'
import { default as retina } from './theme/retina'
import { default as zoomerang } from './theme/zoomerang'

document.addEventListener('DOMContentLoaded', () => {
  animation()
  ecommerce()
  globalNotices()
  navbar()
  offcanvas()
  pricingCharts()
  retina()
  zoomerang()
})
