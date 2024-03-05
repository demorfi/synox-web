<template>
  <b-form>
    <b-form-group
        v-for="setting in pkgSettings"
        :key="setting.name"
        class="mb-3"
        :label="setting.label"
        :label-for="setting.name"
        floating>
      <component :is="`b-form-${component(setting.type)}`"
                 :model-value="setting.value"
                 :type="setting.type"
                 :id="setting.name"
                 :options="setting.params"
                 :placeholder="setting.label"
                 @input="setting.value = $event"
                 @change="setting.value = $event"/>
    </b-form-group>
  </b-form>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';

const {mapGetters, mapActions} = createNamespacedHelpers('packages');

export default {
  props: {
    id: {
      type    : String,
      required: true
    }
  },

  created()
  {
    const {settings, pkgSettings} = this.getPackageById(this.id);
    for (let pkgSetting of pkgSettings) {
      if (pkgSetting in settings) {
        this.pkgSettings.push({name: pkgSetting, ...settings[pkgSetting]});
      }
    }
  },

  data: () => ({
    pkgSettings: []
  }),

  computed: {
    ...mapGetters(['getPackageById', 'getPackageSettings'])
  },

  methods: {
    ...mapActions(['updatePackageSettings']),
    component(type)
    {
      return type === 'select' ? 'select' : 'input';
    },

    saveForm()
    {
      const formData = {};
      const settings = this.getPackageSettings(this.id);

      for (let useSetting of this.pkgSettings) {
        if (useSetting.name in settings && useSetting.value !== settings[useSetting.name].value) {
          formData[useSetting.name] = useSetting.value;
        }
      }

      if (Object.keys(formData).length) {
        this.updatePackageSettings({id: this.id, settings: formData});
      }
    }
  }
}
</script>