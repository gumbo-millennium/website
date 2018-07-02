/**
* Load Cleave on fields that support it
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

// Newer form is not supported :/
const Cleave = require('cleave.js')

const createCleave = (element, config) => {
  element.dataset.cleave = new Cleave(element, config)
}

const init = () => {
  document.querySelectorAll('input[data-cleave=date]').forEach(el => {
    createCleave(el, {
      date: true,
      datePattern: ['d', 'm', 'Y'],
      delimiter: '-'
    })
  })

  document.querySelectorAll('input[data-cleave=time]').forEach(el => {
    createCleave(el, {
      blocks: [2, 2],
      numericOnly: true,
      delimiter: ':'
    })
  })
}

export default init
