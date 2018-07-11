/**
 * Zoomerang
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

import { Zoomerang } from 'zoomerang'

export default () => {
  Zoomerang.config({
    maxHeight: 730,
    maxWidth: 900
  }).listen('[data-trigger="zoomerang"]')
}
