<template>
  <div>
    <h1 class="display-6">
      <span class="position-relative">
        <AppIcon name="box-seam"/>
        <b-badge
            variant="dark"
            class="fs-9"
            pill
            textIndicator>{{ packages.length }}</b-badge>
      </span>
      {{ $options.name }}
    </h1>

    <hr class="my-3">
    <b-row class="row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xxl-6 g-4">
      <b-col
          v-for="pkg in packages"
          :key="pkg.id">
        <PackageItem
            v-bind="pkg"
            @status="changeActivity($event, pkg.id)"
            @auth="showAuthForm(pkg.id, pkg.name)"
            @settings="showSettingsForm(pkg.id, pkg.name, pkg.pkgSettings)"/>
      </b-col>
    </b-row>

    <suspense
        v-if="useAuthForm"
        @resolve="resolveAuthForm">
      <b-modal
          v-model="showAuthModal"
          :title="authForm.title"
          size="sm"
          ok-title="Save"
          cancel-title="Close"
          centered
          @ok="saveAuthForm">
        <PackageFormAuth
            v-model:username="authForm.username"
            v-model:password="authForm.password"/>
      </b-modal>
    </suspense>

    <suspense
        v-if="useSettingsForm"
        @resolve="resolveSettingsForm">
      <b-modal
          v-model="showSettingsModal"
          :title="settingsForm.title"
          size="sm"
          ok-title="Save"
          cancel-title="Close"
          centered
          @ok="saveSettingsForm">
        <PackageFormSettings
            v-model:settings="settingsForm.useSettings"/>
      </b-modal>
    </suspense>
  </div>
</template>

<script>
import {defineAsyncComponent} from 'vue';
import {createNamespacedHelpers} from 'vuex';
import PackageItem from '@/components/PackageItem.vue';
import AppIcon from '@/components/AppIcon.vue';

const {mapState, mapGetters, mapActions} = createNamespacedHelpers('packages');

export default {
  name      : 'Packages',
  components: {
    AppIcon,
    PackageItem,
    PackageFormAuth    : defineAsyncComponent(() => import('@/components/PackageFormAuth.vue')),
    PackageFormSettings: defineAsyncComponent(() => import('@/components/PackageFormSettings.vue')),
  },

  data: () => ({
    useAuthForm      : false,
    useSettingsForm  : false,
    showAuthModal    : false,
    showSettingsModal: false,
    authForm         : {
      id      : '',
      title   : '',
      username: '',
      password: ''
    },
    settingsForm     : {
      id         : '',
      title      : '',
      useSettings: []
    }
  }),

  computed: {
    ...mapState(['packages']),
    ...mapGetters(['getPackageSettings']),
  },

  methods: {
    ...mapActions(['getPackages', 'changePackageActivity', 'updatePackageSettings']),
    changeActivity(active, id)
    {
      this.changePackageActivity({id, active});
    },

    showAuthForm(id, title)
    {
      const {username = '', password = ''} = this.getPackageSettings(id);
      this.authForm = {id, title, username, password};
      return this.useAuthForm ? this.resolveAuthForm() : this.useAuthForm = true;
    },

    showSettingsForm(id, title, pkgSettings)
    {
      const settings = this.getPackageSettings(id);
      const useSettings = [];

      for (let pkgSetting of pkgSettings) {
        if (pkgSetting in settings) {
          settings[pkgSetting]['name'] = pkgSetting;
          useSettings.push({...settings[pkgSetting]});
        }
      }

      this.settingsForm = {id, title, useSettings};
      return this.useSettingsForm ? this.resolveSettingsForm() : this.useSettingsForm = true;
    },

    resolveAuthForm()
    {
      this.$nextTick(() => {
        this.showAuthModal = true;
      });
    },

    resolveSettingsForm()
    {
      this.$nextTick(() => {
        this.showSettingsModal = true;
      });
    },

    saveAuthForm()
    {
      const {id, username, password} = this.authForm;
      const {username: oldUsername = '', password: oldPassword = ''} = this.getPackageSettings(id);

      const settings = {};
      if (username !== oldUsername) {
        settings.username = username;
      }

      if (password !== oldPassword) {
        settings.password = password;
      }

      if (Object.keys(settings).length) {
        this.updatePackageSettings({id, settings});
      }
    },

    saveSettingsForm()
    {
      const {id, useSettings} = this.settingsForm;
      const pkgSettings = this.getPackageSettings(id);

      const settings = {};
      for (let useSetting of useSettings) {
        if (useSetting.name in pkgSettings && useSetting.value !== pkgSettings[useSetting.name].value) {
          settings[useSetting.name] = useSetting.value;
        }
      }

      if (Object.keys(settings).length) {
        this.updatePackageSettings({id, settings});
      }
    }
  }
}
</script>