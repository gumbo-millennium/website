<template>
  <div>
    <!-- Gallery Dialog, teleported to body -->
    <teleport to="body">
      <GumboGalleryOverlay
        ref="overlay"
        :current-image="activeImage"
        @image-changed="imageChanged"
        @close="closeOverlay()"
      />
    </teleport>

    <!-- Photo grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <GumboGalleryTile
        v-for="image in images"
        :key="image.id"
        class="mb-8 masonry-item sm:max-w-1/2 lg:max-w-1/3"
        :image="image"
        @click="() => openOverlay(image)"
      />
    </div>
  </div>
</template>

<script>
import GumboGalleryTile from './GumboGalleryTile.vue'
import GumboGalleryOverlay from './GumboGalleryOverlay.vue'

export default {
  components: {
    GumboGalleryTile,
    GumboGalleryOverlay,
  },
  inject: ['baseTitle', 'images'],
  props: {
    album: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      activeImage: null,
    }
  },
  watch: {
    $route (newRoute, oldRoute) {
      this.handleHistoryChange(newRoute, oldRoute)
    },
  },
  mounted () {
    this.handleHistoryChange(this.$route, { name: 'home' })
  },
  methods: {
    handleHistoryChange (newRoute, oldRoute) {
      if (newRoute.name === 'view' && oldRoute.name !== 'view') {
        const imageId = parseInt(newRoute.params.image, 10)
        const foundImage = this.images.find(image => image.id === imageId)

        this.openOverlay(foundImage, false)
      }

      if (newRoute.name === 'home' && oldRoute.name !== 'home') {
        this.closeOverlay(false)
      }
    },
    imageChanged (image) {
      this.$router.replace({ name: 'view', params: { image: image.id } })
    },
    openOverlay (image, pushRoute = true) {
      this.activeImage = image
      if (pushRoute && this.$route.name !== 'view') {
        this.$router.push({ name: 'view', params: { image: image.id } })
      }
    },
    closeOverlay (pushRoute = true) {
      this.activeImage = null

      document.title = this.baseTitle
      if (pushRoute && this.$route.name !== 'home') {
        this.$router.push({ name: 'home' })
      }
    },
  },
}
</script>
