<template>
  <fa-icon :icon="iconValue" />
</template>

<script>
const groupMap = {
  solid: 'fas',
  regular: 'far',
  light: 'fal',
  duotone: 'fad',
  brand: 'fab'
}

export default {
  props: {
    icon: {
      type: String,
      default: 'box'
    },
    group: {
      type: String,
      default: 'solid',
      validator (group) {
        return ['solid', 'regular', 'light', 'duotone', 'brand'].includes(
          group
        )
      }
    }
  },
  computed: {
    iconValue () {
      return [groupMap[this.group], this.icon]
    }
  }
}
</script>
