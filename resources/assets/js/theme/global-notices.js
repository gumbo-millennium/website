/**
 * Dismiss global notices after 5 seconds, but only when focussed
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

let hidden = null
let visibilityState = null
let visibilityChange = null

// Determine the current state of the window
if (typeof document.hidden !== 'undefined') { // Opera 12.10 and Firefox 18 and later support
  visibilityState = 'visibilityState'
  visibilityChange = 'visibilitychange'
} else if (typeof document.msHidden !== 'undefined') {
  visibilityState = 'msVisibilityState'
  visibilityChange = 'msvisibilitychange'
} else if (typeof document.webkitHidden !== 'undefined') {
  visibilityState = 'webkitVisibilityState'
  visibilityChange = 'webkitvisibilitychange'
}

/**
 * Returns true if the current document is hidden
 *
 * @returns {Boolean}
 */
const isHidden = () => {
  if (hidden === null) {
    return false
  } else {
    return (document[visibilityState] !== 'visible' && document[visibilityState] != null)
  }
}

/**
 * Checks if the notifications should be hidden, and adds a visibilityChange listener if the
 * window is currently invisible. Starts a 5 second timeout if the messages can be dismissed
 *
 * @returns {void}
 */
const shouldHideNotifications = () => {
  if (isHidden) {
    document.addEventListener(visibilityChange, shouldHideNotifications, { once: true })
  } else {
    setTimeout(doHideNotifications, 5000)
  }
}

/**
 * Actually hides all global notifications
 *
 * @returns {void}
 */
const doHideNotifications = () => {
  document.querySelectorAll('.global-notification').forEach(element => {
    element.classList.remove('user-notification')
    element.classList.add('uber-notification-remove')
  })
}

export default () => shouldHideNotifications()
