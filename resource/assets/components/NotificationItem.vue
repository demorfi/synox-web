<template>
  <b-alert
      v-model="secDismissing"
      :variant="type"
      dismissible
      @close-countdown="secCountdown = $event">
    {{ message }}
  </b-alert>
</template>

<script>
export default {
  emits: ['closed'],
  props: {
    type: {
      type     : String,
      default  : 'info',
      validator: value => {
        return ['success', 'danger', 'warning', 'info'].includes(value)
      }
    },

    message: {
      type    : String,
      required: true
    }
  },

  data: () => ({
    secDismissing: 4000,
    secCountdown : 0,
  }),

  watch: {
    secCountdown(sec)
    {
      if (sec === 0) {
        this.$emit('closed');
      }
    }
  }
}
</script>