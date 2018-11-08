/**
 * Admin nav toggle
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

const nodeClick = event => {
  let node = event.target
  let toggle = !node.classList.contains('active')

  node.classList.toggle('active', toggle)
  document.querySelectorAll(node.dataset.target).forEach(node => {
    node.classList.toggle('admin-sidenav--visible', toggle)
  })
}

const init = () => {
  document.querySelectorAll('[data-toggle="admin-nav"]').forEach(node => {
    console.log('CLICK')

    node.addEventListener('click', nodeClick)
  })
}

export default init
