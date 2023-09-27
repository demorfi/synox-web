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
            @toggle-activity="changeActivity($event, pkg.id)"
            @auth="showAuthForm(pkg.id, pkg.name)"/>
      </b-col>
    </b-row>

    <suspense
        v-if="useAuthForm"
        @resolve="resolveAuthForm">
      <b-modal
          v-model="showModal"
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
    PackageFormAuth: defineAsyncComponent(() => import('@/components/PackageFormAuth.vue'))
  },

  data: () => ({
    useAuthForm: false,
    showModal  : false,
    authForm   : {
      id      : '',
      title   : '',
      username: '',
      password: ''
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

    resolveAuthForm()
    {
      this.$nextTick(() => {
        this.showModal = true;
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
    }
  }
}
</script>