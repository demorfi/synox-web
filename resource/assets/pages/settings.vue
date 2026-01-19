<script setup lang="ts">
import {reactive, computed, onBeforeMount} from 'vue';
import {BFormInput, BFormSelect} from 'bootstrap-vue-next';
import {useSettingsStore} from '@/stores/useSettingsStore';
import IconElement from '@/components/elements/Icon.vue';

const settingsStore = useSettingsStore();
const componentMap = {
  Input: BFormInput,
  Select: BFormSelect
};
const fields = reactive({
  app: {
    limitPerPackage: {
      type: 'Input',
      struct: {
        type: 'number',
        id: 'appLimitPerPackage',
        label: 'Limit per package',
        placeholder: 'Enter number limit per package',
        state: null,
        disabled: false
      },
      value: computed(() => settingsStore.settings.app?.limitPerPackage),
      change: (value) => {
        update('app', 'limitPerPackage', value, (value) => /^\d+$/.test(value));
      }
    },
    maxJournalRecords: {
      type: 'Input',
      struct: {
        type: 'number',
        id: 'appMaxJournalRecords',
        label: 'Store only the last number of records',
        placeholder: 'Enter the number of recent log entries to keep',
        state: null,
        disabled: false
      },
      value: computed(() => settingsStore.settings.app?.maxJournalRecords),
      change: (value) => {
        update('app', 'maxJournalRecords', value, (value) => /^[1-9]\d*$/.test(value));
      }
    },
    useJournal: {
      type: 'Select',
      struct: {
        id: 'appUseJournal',
        label: 'Use Journal',
        placeholder: 'Choose to use journal or not',
        disabled: false,
        options: [
          {value: null, text: 'Choose one of the options', disabled: true},
          {value: 'true', text: 'Yes'},
          {value: 'false', text: 'No'}
        ]
      },
      value: computed(() => settingsStore.settings.app?.useJournal),
      change: (value) => {
        update('app', 'useJournal', value);
      }
    }
  }
});

onBeforeMount(() => settingsStore.load());

const update = (type, name, value, validator) => {
  fields[type][name].struct.state = (validator === undefined || validator(value)) ? null : false;
  if (fields[type][name].struct.state !== false) {
    fields[type][name].struct.disabled = true;
    return settingsStore.update(type, name, value)
        .finally(() => {
          fields[type][name].struct.disabled = false;
        });
  }
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
      <template v-for="(type, key) in fields" :key="key">
        <BFormGroup v-for="(field, key) in type" :key="key" class="mb-3" :label="field.struct.label"
                    :label-for="field.struct.id" floating>
          <component :is="componentMap[field.type]" v-bind="field.struct" :model-value="field.value"
                     @update:model-value="field.change"></component>
        </BFormGroup>
      </template>
    </BForm>
  </div>
</template>