<script setup lang="ts">
import {reactive, computed} from 'vue';
import {useStore} from 'vuex';

const store = useStore();
const props = defineProps({
  id: {
    type: String,
    required: true
  }
});

const settings = computed(() => store.getters["packages/getPackageSettings"](props.id));
const form = reactive({
  username: settings.value.username,
  password: settings.value.password,
});

const saveForm = () => {
  const formData = {};

  if (form.username !== settings.value.username) {
    formData.username = form.username;
  }

  if (form.password !== settings.value.password) {
    formData.password = form.password;
  }

  if (Object.keys(formData).length) {
    store.dispatch('packages/updatePackageSettings', {id: props.id, settings: formData})
        // The password is always returned as the string "password". Force a new password value
        .then(({id, state: packageState}) => {
          if ('password' in formData) {
            packageState.settings.password = formData.password;
            store.commit('packages/updatePackageState', {id, packageState});
          }
        });
  }
};

defineExpose({saveForm});
</script>

<template>
  <BForm @submit.prevent="saveForm">
    <BFormGroup class="mb-3" label="Username" label-for="packageUsername" floating>
      <BFormInput v-model="form.username" type="text" id="packageUsername" placeholder="Enter username"
                  autocomplete="off"/>
    </BFormGroup>

    <BFormGroup label="Password" label-for="packagePassword" floating>
      <BFormInput v-model="form.password" type="password" id="packagePassword" placeholder="Enter password"
                  autocomplete="off"/>
    </BFormGroup>
  </BForm>
</template>