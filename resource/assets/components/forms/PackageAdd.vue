<script setup lang="ts">
import {ref, watch} from 'vue';
import {usePackagesStore} from '@/stores/usePackagesStore';

const pkgStore = usePackagesStore();
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
    return pkgStore.upload(formData)
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
    <BFormFile v-model="file" :state="state" accept=".php" required/>
  </BForm>
</template>