<template>
  <b-form>
    <b-form-group
        class="mb-3"
        label="Username"
        label-for="packageUsername"
        floating>
      <b-form-input
          :model-value="username"
          type="text"
          id="packageUsername"
          placeholder="Enter username"
          autocomplete="off"
          @input="$emit('update:username', $event)"/>
    </b-form-group>

    <b-form-group
        label="Password"
        label-for="packagePassword"
        floating>
      <b-form-input
          :model-value="password"
          type="password"
          id="packagePassword"
          placeholder="Enter password"
          autocomplete="off"
          @input="$emit('update:password', $event)"/>
    </b-form-group>
  </b-form>
</template>

<script>
export default {
  emits: ['update:username', 'update:password'],
  props: {
    username: {
      type   : String,
      default: ''
    },

    password: {
      type   : String,
      default: ''
    }
  }
}
</script>