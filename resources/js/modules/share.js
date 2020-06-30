/**
 * Share links
 */

/**
 * Close the nav if open and the user clicked outside it
 * @param {Event} event
 */
const getShareAction = (node) => {
  const shareLink = node.dataset.shareUrl || node.dataShareUrl || node.href || document.location.href
  const shareText = node.dataset.shareText || node.dataShareText || node.title || node.innerText

  // Share
  return (event) => {
    // Stop
    event.preventDefault()

    // Share
    if (!('share' in window.navigator)) {
      return
    }

    window.navigator.share({
      title: shareText,
      url: shareLink
    })
  }
}

/**
 * Binds the menu toggle
 */
const bindShareLinks = () => {
  // Find checkbox in navbar
  document.querySelectorAll('[data-action="share"]').forEach(node => {
    node.addEventListener('click', getShareAction(node), { passive: false })
  })
}

const init = () => {
  bindShareLinks()
}

export default init
