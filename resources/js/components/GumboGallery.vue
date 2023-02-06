<template>
  <div>
    <!-- Gallery Dialog, teleported to body -->
    <teleport to="body">
      <GumboGalleryOverlay
        ref="overlay"
        :images="images"
        :current-image="activeImage"
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
  props: {
    images: {
      type: Array,
      required: true,
    },
  },
  data () {
    return {
      activeImage: null,
    }
  },
  methods: {
    openOverlay (image) {
      this.activeImage = image
    },
    closeOverlay () {
      this.activeImage = null
    },
  },
}
</script>
