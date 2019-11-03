<template>
  <div class="flex items-center text-white text-sm font-bold px-4 py-3" :class="color" role="alert">
    <gumbo-icon :icon="iconName" class="fill-current w-4 h-4 mr-2" />
    <p>
      <slot />
    </p>
  </div>
</template>

<script>
const alertTypes = new Set(["info", "success", "warning", "error"]);

const iconMap = {
  info: "info",
  success: "check",
  warning: "exclamation",
  error: "times-circle"
};

const colorMap = {
  info: "bg-blue-600",
  success: "bg-green-600",
  warning: "bg-yellow-600",
  error: "bg-red-600"
};

export default {
  props: {
    type: {
      type: String,
      default: "info",
      validator(type) {
        return alertTypes.has(type);
      }
    },
    icon: {
      type: String,
      default: null
    }
  },
  computed: {
    iconName() {
      return this.icon || iconMap[this.type];
    },
    color() {
      return colorMap[this.type];
    }
  }
};
</script>
