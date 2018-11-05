/**
 * Global forms
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

const init = function () {
  document.querySelectorAll('[data-action="submit-form"]').forEach(node => {
    let formTarget = document.querySelector(`#${node.dataset.target}`)

    if (!formTarget) {
      return
    }

    node.setAttribute('href', formTarget.action)

    node.addEventListener('click', event => {
      // Cancel click
      event.preventDefault()

      // Form target
      formTarget.submit()
    })
  })
}

export default init
