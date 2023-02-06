<template>
  <div class="rounded shadow grid grid-cols-1 hover:shadow-lg">
    <div class="relative min-h-32 bg-gray-700 rounded-t overflow-hidden">
      <div class="absolute inset-4 flex items-center">
        <img
          src="/images/logo-text-white.svg"
          class="max-w-[80%] h-16 opacity-20 mx-auto"
        >
      </div>
      <picture class="relative">
        <img
          class="rounded w-full object-fit"
          loading="lazy"
          :srcset="imageSources"
          :alt="image.description"
          :style="imageStyles"
        >
      </picture>

      <!-- <div class="absolute bottom-4 right-4">
        <div class="px-2 py-1 rounded bg-white flex items-center">
          <FontAwesomeIcon
            icon="thumbs-up"
            class="h-4 mr-1"
          />
          <span>69</span>
        </div>
      </div> -->
    </div>
    <div class="p-4">
      <h3 class="text-lg font-title truncate">
        <template v-if="image.description">
          {{ image.description }}
        </template>
        <span
          v-else
          class="text-gray-500"
        >
          {{ image.name }}
        </span>
      </h3>
      <div class="flex item-center gap-2">
        <p class="text-gray-700 text-sm flex-grow">
          {{ image.taken_at_label }}
        </p>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    image: {
      type: Object,
      required: true,
    },
  },
  computed: {
    imageSources () {
      return this.image._links.thumbnails.map(source => {
        return `${source.url} ${source.width}w`
      }).join(', ')
    },
    imageStyles () {
      return {
        aspectRatio: this.image.aspect_ratio,
      }
    },
  },
}
</script>
