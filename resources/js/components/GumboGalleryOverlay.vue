<template>
  <dialog
    ref="dialog"
    class="appearance-none"
    @close="dialogClosed"
  >
    <div class="fixed inset-0 z-50 flex flex-col items-stretch h-100vh">
      <div class="flex-grow w-full overflow-hidden bg-black grid grid-cols-1 relative">
        <div class="absolute inset-0 p-16 flex items-center">
          <img
            src="/images/logo-text-white.svg"
            class="max-w-[80%] h-32 opacity-20 object-contain mx-auto"
          >
        </div>
        <img
          class="w-full h-full object-contain relative"
          :class="{ 'opacity-75': loading }"
          :src="activeImageSrc"
          :alt="activeImage?.description"
          :style="imageStyles"
          @load="loading = false"
        >

        <div
          class="absolute top-0 right-0 p-8 cursor-pointer"
          aria-label="Sluiten"
          @click="close()"
        >
          <XMarkIcon class="h-8 text-gray-100" />
        </div>

        <template v-if="images.length > 1">
          <div
            class="absolute left-0 px-8 inset-y-24 flex items-center cursor-pointer"
            aria-label="Vorige afbeelding"
            @click="previous()"
          >
            <ChevronLeftIcon class="h-8 text-gray-100" />
          </div>

          <div
            class="absolute right-0 px-8 inset-y-24 flex items-center cursor-pointer"
            aria-label="Volgende afbeelding"
            @click="next()"
          >
            <ChevronRightIcon class="h-8 text-gray-100" />
          </div>
        </template>
      </div>

      <div class="flex-none w-full bg-gray-900 p-4 flex gap-4 items-start lg:items-center">
        <div
          id="metadata"
          class="flex-grow"
        >
          <h1
            v-if="activeImage?.description"
            class="text-lg font-bold font-title text-gray-100 mb-2"
          >
            {{ activeImage?.description }}
          </h1>
          <div class="metadata">
            <time
              v-if="activeImage?.taken_at_label"
              aria-label="Gemaakt op"
              :datetime="activeImage?.created_at"
            >
              <ClockIcon class="h-4" />
              {{ activeImage?.taken_at_label }}
            </time>
            <data
              v-if="activeImage?.exif?.makeModel"
              aria-label="Camera"
            >
              <CameraIcon class="h-4" />
              {{ activeImage?.exif?.makeModel }}
            </data>
            <time
              aria-label="Geupload op"
              :datetime="activeImage?.created_at"
            >
              <SparklesIcon class="h-4" />
              {{ activeImage?.created_at_label }}
            </time>
            <data aria-label="Bestandsnaam">
              <DocumentIcon class="h-4" />
              {{ activeImage?.name }}
            </data>
            <data aria-label="Foto nummer">
              <HashtagIcon class="h-4" />
              {{ imagePosition }}
            </data>
            <a
              :href="activeImage?._links.download"
              :download="activeImage?.name"
            >
              <ArrowDownTrayIcon class="h-4" />
              Download
            </a>
          </div>
        </div>
        <div class="flex-none flex flex-col gap-2 sm:flex-row text-center">
          <GumboButton
            v-if="shareable"
            color="secondary"
            @click="share()"
          >
            <span class="hidden sm:inline">Deel</span>
            <span class="sm:hidden">Delen</span>
          </GumboButton>
        </div>
      </div>
    </div>
  </dialog>
</template>
<script>
import GumboButton from './GumboButton.vue'
import {
  ArrowDownTrayIcon,
  CameraIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClockIcon,
  DocumentIcon,
  HashtagIcon,
  SparklesIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

export default {
  components: {
    ArrowDownTrayIcon,
    CameraIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ClockIcon,
    DocumentIcon,
    HashtagIcon,
    GumboButton,
    SparklesIcon,
    XMarkIcon,
  },
  inject: ['baseTitle', 'images'],
  props: {
    currentImage: {
      type: Object,
      default: null,
    },
  },

  emits: ['imageChanged'],
  data () {
    return {
      open: false,
      loading: false,
      activeImageIndex: null,
    }
  },
  computed: {
    shareable () {
      return typeof navigator.share === 'function' && this.activeImage
    },
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
    imageStyles () {
      return {
        aspectRatio: this.activeImage?.aspect_ratio,
      }
    },
    imagePosition () {
      return `${this.activeImageIndex + 1} / ${this.images.length}`
    },
  },
  watch: {
    currentImage (image) {
      // If the image is unset, and we're open, close the dialog
      if (!image) {
        if (this.open) {
          this.close()
        }

        return
      }

      // Find the image
      const foundImageIndex = this.images.findIndex(possibleImage => possibleImage.id === image.id)
      if (foundImageIndex === -1) {
        return
      }

      // Change the loading state, if the image actually changed.
      if (this.activeImageIndex !== foundImageIndex) {
        this.loading = true
      }

      // Update value
      this.activeImageIndex = foundImageIndex

      // Open the dialog if it's not already open
      if (!this.open) {
        this.$refs.dialog.showModal()
        this.open = true
      }
    },
    activeImageIndex (newIndex) {
      // Update the document title, if we're looking at an image
      if (this.activeImage && newIndex !== null) {
        document.title = `${this.activeImage.description ?? this.activeImage.name} - ${this.baseTitle}`
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
    close () {
      this.$refs.dialog.close()
    },
    dialogClosed () {
      this.open = false
    },
    next () {
      this.activeImageIndex = this.nextImageIndex
      this.$emit('imageChanged', this.images[this.activeImageIndex])
    },
    previous () {
      this.activeImageIndex = this.previousImageIndex
      this.$emit('imageChanged', this.images[this.activeImageIndex])
    },
    share () {
      if (!this.shareable || !this.activeImage) {
        return
      }

      const sharedImage = this.activeImage

      // Stream the image to a Blob
      fetch(sharedImage._links.download)
        .then(response => response.blob())
        .then(blob => {
          // Share the image
          navigator.share({
            title: sharedImage.name,
            text: sharedImage.description,
            files: [blob],
          })
        })
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

<style scoped>
.metadata {
  @apply text-sm text-gray-300 flex items-center flex-wrap gap-x-4 gap-y-2;
}
.metadata > * {
  @apply flex items-center gap-2;
}
.metadata a {
  @apply text-gray-300 no-underline;
}
.metadata a:hover, .metadata a:focus {
  @apply text-gray-100 underline;
}
</style>
