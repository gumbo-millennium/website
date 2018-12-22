/**
 * Loads dropzone on [data-content="dropzone"] elements
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

import Dropzone from './gumbo-dropzone'
import jQuery from 'jquery'

// eslint-disable-next-line no-unused-vars
let activeZone = null

const buildForm = node => {
  activeZone = new Dropzone(node)

  jQuery(node).modal({
    keyboard: false,
    backdrop: true,
    show: false
  })

  for (let button of document.querySelectorAll('[data-upload-action=open]')) {
    console.info('Binding click action on %o.', button)

    button.addEventListener('click', event => {
      event.preventDefault()
      jQuery(node).modal('show')
    })
  }
}

const init = () => {
  let uploadElement = null
  for (let node of document.querySelectorAll('[data-content="upload-form"]')) {
    uploadElement = node
  }

  // Create new zone
  if (uploadElement) {
    buildForm(uploadElement)
  }
}

export default init
