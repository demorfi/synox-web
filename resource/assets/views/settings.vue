<template>
  <div>
    <h1 class="display-6">
      <AppIcon name="gear"/>
      {{ $options.name }}
    </h1>

    <hr class="my-3">
    <b-form>
      <b-form-group
          class="mb-3"
          label="Limit per package"
          label-for="appLimitPerPackage"
          floating>
        <b-form-input
            :model-value="appState.limitPerPackage"
            :state="appFields.limitPerPackage.isValid"
            :disabled="appFields.limitPerPackage.isDisabled"
            type="number"
            id="appLimitPerPackage"
            placeholder="Enter number limit per package"
            lazy
            number
            required
            @change="updateAppLimitPerPackage"/>
      </b-form-group>

      <b-form-group
          class="mb-3"
          label="Store only the last number of records"
          label-for="appMaxJournalRecords"
          floating>
        <b-form-input
            :model-value="appState.maxJournalRecords"
            :state="appFields.maxJournalRecords.isValid"
            :disabled="appFields.maxJournalRecords.isDisabled"
            type="number"
            id="appMaxJournalRecords"
            placeholder="Enter the number of recent log entries to keep"
            lazy
            number
            required
            @change="updateAppMaxJournalRecords"/>
      </b-form-group>

      <b-form-group
          class="mb-3"
          label="Use Journal"
          label-for="appUseJournal"
          floating>
        <b-form-select
            :model-value="appState.useJournal"
            :options="appFields.useJournal.options"
            :disabled="appFields.useJournal.isDisabled"
            id="appUseJournal"
            placeholder="Choose to use journal or not"
            @change="updateAppUseJournal"/>
      </b-form-group>
    </b-form>
  </div>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';
import AppIcon from '@/components/AppIcon.vue';

const {mapActions, mapState} = createNamespacedHelpers('settings');

export default {
  name      : 'Settings',
  components: {
    AppIcon
  },

  async created()
  {
    await this.getSettings();
  },

  data: () => ({
    appFields: {
      limitPerPackage  : {isValid: null, isDisabled: false},
      maxJournalRecords: {isValid: null, isDisabled: false},
      useJournal       : {
        isDisabled: false,
        options   : [
          {value: null, text: 'Choose one of the options', disabled: true},
          {value: 'true', text: 'Yes'},
          {value: 'false', text: 'No'}
        ]
      }
    }
  }),

  computed: {
    ...mapState({appState: 'app'})
  },

  methods: {
    ...mapActions(['getSettings', 'updateSetting']),
    updateAppSetting(name, value, validator)
    {
      this.appFields[name].isValid = (validator === undefined || validator(value)) ? null : false;
      if (this.appFields[name].isValid !== false) {
        this.appFields[name].isDisabled = true;
        return this.updateSetting({type: 'app', name, value})
            .finally(() => {
              this.appFields[name].isDisabled = false;
            });
      }
    },

    updateAppLimitPerPackage(value)
    {
      this.updateAppSetting('limitPerPackage', value, (value) => /^\d+$/.test(value));
    },

    updateAppMaxJournalRecords(value)
    {
      this.updateAppSetting('maxJournalRecords', value, (value) => /^[1-9]\d*$/.test(value));
    },

    updateAppUseJournal(value)
    {
      this.updateAppSetting('useJournal', value);
    }
  }
}
</script>