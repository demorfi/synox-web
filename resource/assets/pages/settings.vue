<script setup lang="ts">
import {reactive, computed, onBeforeMount} from 'vue';
import {useStore} from 'vuex';
import IconElement from '@/components/elements/Icon.vue';

const store = useStore();
const appFields = reactive({
  limitPerPackage: {
    isValid: null,
    isDisabled: false
  },
  maxJournalRecords: {
    isValid: null,
    isDisabled: false
  },
  useJournal: {
    isDisabled: false,
    options: [
      {value: null, text: 'Choose one of the options', disabled: true},
      {value: 'true', text: 'Yes'},
      {value: 'false', text: 'No'}
    ]
  }
});

const appState = computed(() => store.state.settings.app);

onBeforeMount(() => store.dispatch('settings/getSettings'));

const __updateAppSetting = (name, value, validator) => {
  appFields[name].isValid = (validator === undefined || validator(value)) ? null : false;
  if (appFields[name].isValid !== false) {
    appFields[name].isDisabled = true;
    return store.dispatch('settings/updateSetting', {type: 'app', name, value})
        .finally(() => {
          appFields[name].isDisabled = false;
        });
  }
}

const updateAppLimitPerPackage = (value) => {
  __updateAppSetting('limitPerPackage', value, (value) => /^\d+$/.test(value));
}

const updateAppMaxJournalRecords = (value) => {
  __updateAppSetting('maxJournalRecords', value, (value) => /^[1-9]\d*$/.test(value));
}

const updateAppUseJournal = (value) => {
  __updateAppSetting('useJournal', value);
}
</script>

<template>
  <div>
    <h1 class="display-6">
      <IconElement name="gear"/>
      Settings
    </h1>

    <hr class="my-3">
    <BForm>
      <BFormGroup class="mb-3" label="Limit per package" label-for="appLimitPerPackage" floating>
        <BFormInput type="number" id="appLimitPerPackage" placeholder="Enter number limit per package"
                    :model-value="appState.limitPerPackage" :state="appFields.limitPerPackage.isValid"
                    :disabled="appFields.limitPerPackage.isDisabled" @change="updateAppLimitPerPackage"
                    lazy number required/>
      </BFormGroup>

      <BFormGroup class="mb-3" label="Store only the last number of records" label-for="appMaxJournalRecords" floating>
        <BFormInput type="number" id="appMaxJournalRecords" placeholder="Enter the number of recent log entries to keep"
                    :model-value="appState.maxJournalRecords" :state="appFields.maxJournalRecords.isValid"
                    :disabled="appFields.maxJournalRecords.isDisabled" @change="updateAppMaxJournalRecords"
                    lazy number required/>
      </BFormGroup>

      <BFormGroup class="mb-3" label="Use Journal" label-for="appUseJournal" floating>
        <BFormSelect id="appUseJournal" placeholder="Choose to use journal or not"
                     :model-value="appState.useJournal" :options="appFields.useJournal.options"
                     :disabled="appFields.useJournal.isDisabled" @change="updateAppUseJournal"/>
      </BFormGroup>
    </BForm>
  </div>
</template>