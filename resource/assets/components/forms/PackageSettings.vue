<script setup lang="ts">
import {ref, computed, onBeforeMount} from 'vue';
import {useStore} from 'vuex';

const store = useStore();
const props = defineProps({
  id: {
    type: String,
    required: true
  }
});

const pkg = computed(() => store.getters["packages/getPackageById"](props.id));
const settings = computed(() => store.getters["packages/getPackageSettings"](props.id));
const pkgSettings = ref([]);

onBeforeMount(() => {
  for (let pkgSetting of pkg.value.pkgSettings) {
    if (pkgSetting in settings.value) {
      pkgSettings.value.push({name: pkgSetting, ...settings.value[pkgSetting]});
    }
  }
});

const component = (type) => type === 'select' ? 'BFormSelect' : 'BFormInput';

const saveForm = () => {
  const formData = {};

  for (let useSetting of pkgSettings.value) {
    if (useSetting.name in settings.value && useSetting.value !== settings.value[useSetting.name].value) {
      formData[useSetting.name] = useSetting.value;
    }
  }

  if (Object.keys(formData).length) {
    store.dispatch('packages/updatePackageSettings', {id: props.id, settings: formData});
  }
};

defineExpose({saveForm});
</script>

<template>
  <BForm @submit.prevent="saveForm">
    <BFormGroup v-for="setting in pkgSettings" :key="setting.name" :label="setting.label" :label-for="setting.name"
                class="mb-3" floating>
      <component :is="component(setting.type)" :model-value="setting.value" :type="setting.type"
                 :id="setting.name" :options="setting.params" :placeholder="setting.label"
                 @input="setting.value = $event" @change="setting.value = $event"/>
    </BFormGroup>
  </BForm>
</template>