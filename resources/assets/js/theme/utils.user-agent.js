/**
 * User Agent detection, based on MobileDetect
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

import MobileDetect from 'mobile-detect'

/**
 * Handles proxying requests to the MobileDetect class
 */
export default class UserAgent {
  constructor () {
    this.mobileDetect = new MobileDetect(navigator.userAgent)
  }

  /**
   * @returns {Boolean}
   */
  isFirefox () {
    return this.mobileDetect.is('Firefox')
  }

  /**
   * @returns {Boolean}
   */
  isSafari () {
    return this.mobileDetect.is('Safari')
  }

  /**
   * @returns {Boolean}
   */
  isOpera () {
    return this.mobileDetect.is('Opera')
  }

  /**
   * True if the browser is Google Chrome or a Chrome-like browser (think WebKit).
   *
   * @returns {Boolean}
   */
  isChrome () {
    return this.mobileDetect.is('Chrome')
  }

  /**
   * @returns {Boolean}
   */
  isEdge () {
    return this.mobileDetect.is('Edge')
  }

  /**
   * @returns {Boolean}
   */
  isInternetExplorer () {
    return this.mobileDetect.is('IE')
  }

  /**
   * @returns {Boolean}
   */
  isMobile () {
    return this.mobileDetect.mobile() !== null
  }

  /**
   * @returns {Boolean}
   */
  isPhone () {
    return this.mobileDetect.phone() !== null
  }

  /**
   * @returns {Boolean}
   */
  isTablet () {
    return this.mobileDetect.tablet() !== null
  }

  /**
   * @returns {Boolean}
   */
  isDesktop () {
    return !(this.isMobile() || this.isTablet())
  }
}
