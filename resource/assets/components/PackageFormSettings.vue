<template>
  <b-form>
    <b-form-group
        v-for="setting in settings"
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
export default {
  props  : {
    settings: {
      type    : Array,
      required: true
    }
  },
  methods: {
    component(type)
    {
      return type === 'select' ? 'select' : 'input';
    }
  }
}
</script>