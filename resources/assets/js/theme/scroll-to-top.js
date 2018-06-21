/**
* Handles the "Scroll to top" button
*
* @author Roelof Roos <github@roelof.io>
*/

// Load plugin
import throttle from 'lodash.throttle'

// Get scroll top
const scrollTopButton = document.querySelector('.scroll-top')

if (scrollTopButton !== null) {
  window.addEventListener('scroll', throttle(() => {
    console.log('Boop')

    scrollTopButton.classList.toggle('scroll-top-visible', window.scrollY > 100)
  }, 100))

  scrollTopButton.addEventListener('click', (event) => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    })
    event.preventDefault()
  })
}
