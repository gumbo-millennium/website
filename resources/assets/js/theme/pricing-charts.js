/**
 * Handle pricing chart period changes (e.g. 1 month vs 1 year)
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

// Holder for period tabs and price fields
let tabs = null
let prices = null

/**
 * Called when the user clicks a pricing tab, causing the prices to change
 *
 * @param {HTMLElement} element
 */
const tabClicked = element => {
  // Disable non-clicked tabs
  tabs.forEach(node => {
    element.classList.toggle('active', node === element)
  })

  // Get period name
  var period = element.dataset.get('tab')

  // Collect prices for the given period, and also get all those outside of it
  let inPeriod = prices.filter(entry => entry.classList.contains(period))
  let outPeriod = prices.filter(entry => !entry.classList.contains(period))

  // Apply classes
  inPeriod.forEach(node => node.classList.add('active'))
  outPeriod.forEach(node => node.classList.add('go-out'))

  // Disable the 'go-out' and 'active' classes after animation completes
  setTimeout(() => {
    requestAnimationFrame(() => {
      outPeriod.forEach(node => node.classList.remove('go-out', 'active'))
    })
  }, 250)
}

export default () => {
  // Get all price tabs
  tabs = document.querySelectorAll('.pricing-charts-tabs .tab')

  // Get all total prices
  prices = Array.from(document.querySelectorAll('.pricing-charts .chart header .price'))

  // Bind click events
  tabs.forEach(element => {
    element.addEventListener('click', () => {
      tabClicked(element)
    })
  })
}
