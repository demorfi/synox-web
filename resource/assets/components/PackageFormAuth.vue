<template>
  <b-form>
    <b-form-group
        class="mb-3"
        label="Username"
        label-for="packageUsername"
        floating>
      <b-form-input
          v-model="username"
          type="text"
          id="packageUsername"
          placeholder="Enter username"
          autocomplete="off"/>
    </b-form-group>

    <b-form-group
        label="Password"
        label-for="packagePassword"
        floating>
      <b-form-input
          v-model="password"
          type="password"
          id="packagePassword"
          placeholder="Enter password"
          autocomplete="off"/>
    </b-form-group>
  </b-form>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';

const {mapGetters, mapActions, mapMutations} = createNamespacedHelpers('packages');

export default {
  props: {
    id: {
      type    : String,
      required: true
    }
  },

  created()
  {
    const {username, password} = this.getPackageSettings(this.id);
    this.username = username;
    this.password = password;
  },

  data: () => ({
    username: '',
    password: ''
  }),

  computed: {
    ...mapGetters(['getPackageSettings'])
  },

  methods: {
    ...mapActions(['updatePackageSettings']),
    ...mapMutations(['updatePackageState']),
    saveForm()
    {
      const formData = {};
      const {username: oldUsername = '', password: oldPassword = ''} = this.getPackageSettings(this.id);

      if (this.username !== oldUsername) {
        formData.username = this.username;
      }

      if (this.password !== oldPassword) {
        formData.password = this.password;
      }

      if (Object.keys(formData).length) {
        this.updatePackageSettings({id: this.id, settings: formData})
            // The password is always returned as the string "password". Force a new password value
            .then(({id, state: packageState}) => {
              if ('password' in formData) {
                packageState.settings.password = formData.password;
                this.updatePackageState({id, packageState});
              }
            });
      }
    }
  }
}
</script>