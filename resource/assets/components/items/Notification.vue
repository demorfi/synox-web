<script setup lang="ts">
import {ref, watch} from 'vue';

const emit = defineEmits(['closed']);
const props = defineProps({
  variant: {
    type: String,
    default: 'info',
    validator: (value) => {
      return ['success', 'danger', 'warning', 'info'].includes(value)
    }
  },

  message: {
    type: String,
    required: true
  }
});

const secDismissing = ref(4000);
const secCountdown = ref(0);

watch(secCountdown, (sec) => {
  if (sec === 0) {
    emit('closed');
  }
});
</script>

<template>
  <BAlert v-model="secDismissing" :variant @close-countdown="secCountdown = $event" dismissible>
    {{ message }}
  </BAlert>
</template>