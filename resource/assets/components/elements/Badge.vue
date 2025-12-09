<script setup lang="ts">
import {computed} from 'vue';
import {useStore} from 'vuex';

const store = useStore();
const props = defineProps({
  id: String,
  payload: {
    type: Function,
    default: () => true
  },
  variant: String
});

const content = computed(() => props.payload(store));
</script>

<template>
  <BBadge v-if="content" :id :variant pill>
    <slot>{{ content }}</slot>
  </BBadge>
</template>