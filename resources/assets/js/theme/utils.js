/**
* Theme utils
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

import UserAgent from './utils.user-agent'
const ua = new UserAgent()

export default class Utils {
  static UserAgent () {
    return ua
  }
}
