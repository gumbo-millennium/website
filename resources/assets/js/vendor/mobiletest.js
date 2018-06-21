/**
 * Checks for mobile devices in a crappy way
 *
 * @author Roelof Roos <github@roelof.io>
 */

const regex = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i
const isMobile = regex.test(navigator.userAgent)

export default isMobile
