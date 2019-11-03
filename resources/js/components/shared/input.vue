<template>
  <div :class="{
    'mb-6' : last,
    'mb-4' : !last
  }">
    <label :for="name" class="block text-gray-700 text-sm font-bold mb-2">
      <slot />
    </label>
    <input
      :id="name"
      :type="type"
      :name="name"
      v-model="value"
      :required="required"
      :autocomplete="autocomplete"
      :autofocus="autofocus"
      class="form-input mt-1 block w-full"
    />
  </div>
</template>

<script>
export default {
  props: {
    value: {},
    name: String,
    autocomplete: String,
    type: {
      type: String,
      default: "text"
    },
    required: {
      type: Boolean,
      default: false
    },
    autofocus: {
      type: Boolean,
      default: false
    },
    last: {
      type: Boolean,
      default: false
    }
  }
};
</script>
