<template>
  <dialog
    ref="dialog"
    class="appearance-none"
    @close="dialogClosed"
  >
    <div class="fixed inset-0 z-50 flex flex-col items-stretch h-100vh">
      <div class="flex-grow w-full overflow-hidden bg-black grid grid-cols-1">
        <img
          class="w-full h-full object-contain"
          :src="activeImageSrc"
          :alt="activeImage?.description"
        >
      </div>

      <div class="flex-none w-full bg-gray-900 p-4 flex gap-4 items-start lg:items-center">
        <div
          id="metadata"
          class="flex-grow"
        >
          <h1 class="text-lg font-bold font-title text-gray-100 mb-2">
            <template v-if="activeImage?.description">
              {{ activeImage?.description }}
            </template>
            <span
              v-else
              class="text-gray-300"
            >
              Geen omschrijving 🤷‍♂️
            </span>
          </h1>
          <div class="text-sm text-gray-300 flex items-center flex-wrap gap-2">
            <span>Gemaakt op: {{ activeImage?.taken_at_label ?? 'onbekend' }}</span>
            <span>Bestandsnaam: {{ activeImage?.name }}</span>
            <span>Geupload op: {{ activeImage?.created_at_label }}</span>
          </div>
        </div>
        <div class="flex-none flex flex-col gap-2 sm:flex-row text-center">
          <GumboButton
            ref="previousButton"
            @click="previous()"
          >
            Vorige
          </GumboButton>
          <GumboButton
            ref="nextButton"
            @click="next()"
          >
            Volgende
          </GumboButton>
        </div>
      </div>
    </div>
  </dialog>
</template>
<script>
import GumboButton from './GumboButton.vue'

export default {
  components: {
    GumboButton,
  },
  props: {
    images: {
      type: Array,
      default: () => [],
    },
    currentImage: {
      type: Object,
      default: null,
    },
  },
  data () {
    return {
      open: false,
      activeImageIndex: null,
    }
  },
  computed: {
    activeImage () {
      return this.activeImageIndex !== null ? this.images[this.activeImageIndex] : null
    },
    activeImageSrc () {
      // Find the largest thumbnail
      return this.activeImage?._links.thumbnails.reduce((largest, current) => {
        return current.width > largest.width ? current : largest
      }, { width: 0 })?.url
    },
    nextImageIndex () {
      return this.images[this.activeImageIndex + 1] ? this.activeImageIndex + 1 : 0
    },
    previousImageIndex () {
      return this.images[this.activeImageIndex - 1] ? this.activeImageIndex - 1 : this.images.length - 1
    },
  },
  watch: {
    currentImage (image) {
      // New image must be set
      if (!image) {
        return
      }

      // Find the image
      const foundImageIndex = this.images.findIndex(possibleImage => possibleImage.id === image.id)
      if (foundImageIndex === -1) {
        return
      }
      this.activeImageIndex = foundImageIndex

      // Open the dialog if it's not already open
      if (!this.open) {
        this.$refs.dialog.showModal()
        this.open = true
      }
    },
    open (newOpen, oldOpen) {
      document.body.classList.toggle('overflow-hidden', newOpen)
    },
  },
  mounted () {
    // Listen to next/previous keys
    document.addEventListener('keydown', this.handleKeyDown)
  },
  beforeUnmount () {
    // Remove listener
    document.removeEventListener('keydown', this.handleKeyDown)
  },
  methods: {
    dialogClosed () {
      this.open = false
    },
    next () {
      this.activeImageIndex = this.nextImageIndex
      this.$refs.nextButton.focus()
    },
    previous () {
      this.activeImageIndex = this.previousImageIndex
      this.$refs.previousButton.focus()
    },
    handleKeyDown (event) {
      if (!this.open) {
        return
      }

      if (event.key === 'ArrowRight' || event.key === 'd' || event.key === ' ') {
        // Handle the next key event
        event.preventDefault()
        this.next()
      } else if (event.key === 'ArrowLeft' || event.key === 'a') {
        // Handle the previous key events
        event.preventDefault()
        this.previous()
      }

      // Escape is handled by the browser
    },
  },
}
</script>
