<template>
  <button :class="className" :type="type" :disabled="disabled" autocomplete="off">
    <slot>Button</slot>
  </button>
</template>

<script>
export default {
  props: {
    block: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      default: "submit",
      validator(value) {
        return ["submit", "reset", "button"].includes(value);
      }
    }
  },
  computed: {
    className() {
      return {
        "btn btn-brand": true,
        "btn-brand--disabled": this.disabled,
        "w-full block": this.block
      };
    }
  }
};
</script>
