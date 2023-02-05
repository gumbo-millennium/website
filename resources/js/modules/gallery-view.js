import { createApp } from 'vue'
import GumboGallery from '../components/GumboGallery.vue'

/**
 * Initialises the gallery view on this node, all children being photos.
 * @param {HTMLElement} node
 */
const initialiseGallery = (node) => {
  // Find the data node
  const galleryDataObject = node.querySelector('script[data-content="gallery-data"]')
  if (!galleryDataObject) {
    console.error('No gallery data found for gallery', node)
    return
  }

  // Parse data
  const galleryData = JSON.parse(galleryDataObject.innerHTML)

  // Make Vue app
  createApp(GumboGallery, galleryData).mount(node)
}

/**
 * Initialises all galleries on the page.
 */
export const init = () => {
  document.querySelectorAll('[data-content="gallery"]').forEach(initialiseGallery)
}
