/**
* Handles retina images, if a srcset is not specified on the node
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

export default () => {
  // Don't act on devices with a regular DPI
  if (window.devicePixelRatio < 1.2) {
    return
  }

  document.querySelectorAll('[data-2x]').forEach(node => {
    let retinaUrl = node.dataset.get('data-2x')
    if (node.tagName !== 'IMG') {
      node.style.backgroundImage = `url("${retinaUrl}")`
    } else {
      // Report if changing URLs on a srcset
      if (node.srcset != null) {
        console.log('Setting image url to %s whilst %O has a srcset', retinaUrl, node)
      }

      // Change source
      node.setAttribute('src', retinaUrl)
    }
  })
}
