import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import GumboGallery from '../components/GumboGallery.vue'

// Determine default title
const baseTitle = document.title

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

  // Construct routes
  const albumLink = new URL(galleryData._links.self)
  const galleryRoutes = [
    { path: '/', name: 'home', components: [] },
    { path: '/photo/:image', name: 'view', components: [] },
  ]

  // Construct router
  const router = createRouter({
    history: createWebHistory(albumLink.pathname),
    routes: galleryRoutes,
  })

  const vueData = {
    album: {
      ...galleryData,
      images: null,
    },
  }

  // Make Vue app
  createApp(GumboGallery, vueData)
    .use(router)
    .provide('images', galleryData.images)
    .provide('baseTitle', baseTitle)
    .mount(node)
}

/**
 * Initialises all galleries on the page.
 */
export const init = () => {
  document.querySelectorAll('[data-content="gallery"]').forEach(initialiseGallery)
}
