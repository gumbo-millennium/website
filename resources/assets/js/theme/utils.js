/**
* Theme utils
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

import UserAgent from './utils.user-agent'
import CssUtils from './utils.css'
const ua = new UserAgent()

export default {
  UserAgent: ua,
  GetVariable: CssUtils.unit,
  GetNumber: CssUtils.value
}
