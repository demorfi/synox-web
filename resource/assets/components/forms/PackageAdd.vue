<script setup lang="ts">
import {ref, watch} from 'vue';
import {useStore} from 'vuex';

const store = useStore();
const file = ref(null);
const state = ref(null);

watch(file, () => state.value = null);

const reset = () => file.value = null;

const validate = () => {
  if (file.value === null) {
    state.value = false;
    return false;
  }
  return true;
};

const upload = () => {
  if (validate()) {
    const formData = new FormData();
    formData.append('package', file.value);
    return store.dispatch('packages/uploadPackage', formData)
        .catch((message) => {
          state.value = false;
          throw new Error(message);
        });
  }
  return new Promise((resolve, reject) => reject());
}

defineExpose({reset, validate, upload});
</script>

<template>
  <BForm @submit.prevent="upload">
    <BAlert :model-value="true" variant="warning" class="fs-8">
      <h6 class="alert-heading">Attention!</h6>
      <p class="mb-0">Upload package from trusted sources and only at your own risk!</p>
    </BAlert>
    <BFormFile v-model="file" :state="state" accept="application/x-php" required/>
  </BForm>
</template>