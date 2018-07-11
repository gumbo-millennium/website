/**
* Off canvas animations
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

import jQuery from 'jquery'

export default () => {
  let offWrapper = jQuery('.off-wrapper')
  let toggler = offWrapper.find('.navbar-toggler')
  let offContent = offWrapper.find('.off-wrapper-content')
  let offMenu = offWrapper.find('.off-wrapper-menu')

  offContent.click(() => {
    offWrapper.removeClass('active')
  })

  toggler.click(event => {
    event.stopPropagation()
    offWrapper.toggleClass('active')
  })

  offMenu.find('.dropdown-item').click(event => {
    event.stopPropagation()
  })
}
