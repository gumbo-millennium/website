/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import Axios from 'axios'

// Add X-Requested-With header
Axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */
const token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
  Axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content')
} else {
  console.warn('CSRF token not found')
}

export default Axios
