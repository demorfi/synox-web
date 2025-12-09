<script setup lang="ts">
import {ref, computed, watchEffect, inject} from 'vue';
import {useStore} from 'vuex';
import {prefersSchemeInjectionKey} from '@/store/keys';

const store = useStore();
const props = defineProps({
  id: String,
  payload: {
    type: Function,
    default: () => true
  },
  variant: String
});

const pScheme = inject(prefersSchemeInjectionKey);
const content = computed(() => props.payload(store));
const variant = ref(props.variant);

watchEffect(() => {
  if (props.variant === undefined) {
    variant.value = pScheme.invert;
  }
});
</script>

<template>
  <BBadge v-if="content" :id :variant pill>
    <slot>{{ content }}</slot>
  </BBadge>
</template>