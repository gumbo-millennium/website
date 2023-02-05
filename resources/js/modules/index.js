import navbar from './navbar'
import share from './share'
import countdowns from './countdowns'
import galleryFilepond from './gallery-filepond'
import scanner from './scanner'
import { init as galleryView } from './gallery-view'

const init = () => {
  navbar()
  share()
  countdowns()
  galleryFilepond()
  scanner()
  galleryView()
}

export default init
