<template>
  <b-card
      :header-class="[`text-bg-${bgVariant}`, 'rounded-0']"
      bg-variant="light"
      footer-class="rounded-0 fs-8"
      class="h-100 border-0 rounded-0 border-bottom">

    <template #header>
      <b-button-group>
        <b-button
            v-if="usesAuth"
            variant="outline-warning"
            size="sm"
            @click="showForm('auth')">
          <AppIcon name="key-fill"/>
        </b-button>
        <b-button
            v-if="pkgSettings.length"
            :variant="enabled ? 'outline-warning' : 'outline-light'"
            size="sm"
            @click="showForm('settings')">
          <AppIcon name="gear"/>
        </b-button>
        <b-button
            :variant="enabled ? 'outline-warning' : 'outline-light'"
            :disabled="!available"
            size="sm"
            @click="changeActivity">
          {{ enabled ? 'Disable' : 'Enable' }}
        </b-button>
      </b-button-group>
    </template>

    <template #footer>
      <b-row>
        <dl class="col-6 mb-0">
          <dt>Type</dt>
          <dd>{{ type }} &dash; {{ subtype }}</dd>
        </dl>
        <dl class="col-6 mb-0">
          <dt>Version</dt>
          <dd>{{ version }}</dd>
        </dl>
        <dl
            v-if="requires.length"
            class="col-12 mb-0">
          <dt>Requires</dt>
          <dd>{{ requires.join(',') }}</dd>
        </dl>
      </b-row>
    </template>

    <b-card-title tag="h5">{{ name }}</b-card-title>
    <b-card-text class="text-wrap fw-lighter">{{ description }}</b-card-text>
  </b-card>

  <suspense
      v-if="forms.auth.use"
      @resolve="resolveForm('auth')">
    <b-modal
        v-model="forms.auth.show"
        :title="name"
        size="sm"
        ok-title="Save"
        cancel-title="Close"
        centered
        @ok="saveForm('auth')">
      <PackageFormAuth
          v-bind="{id}"
          ref="auth"/>
    </b-modal>
  </suspense>

  <suspense
      v-if="forms.settings.use"
      @resolve="resolveForm('settings')">
    <b-modal
        v-model="forms.settings.show"
        :title="name"
        size="md"
        ok-title="Save"
        cancel-title="Close"
        centered
        @ok="saveForm('settings')">
      <PackageFormSettings
          :id="id"
          ref="settings"/>
    </b-modal>
  </suspense>
</template>

<script>
import {defineAsyncComponent} from 'vue';
import {createNamespacedHelpers} from 'vuex';
import AppIcon from '@/components/AppIcon.vue';

const {mapState, mapGetters, mapActions} = createNamespacedHelpers('packages');

export default {
  props: {
    id         : String,
    name       : String,
    type       : String,
    subtype    : String,
    version    : String,
    description: String,
    enabled    : Boolean,
    available  : Boolean,
    onlyAllowed: Object,
    requires   : Array,
    usesAuth   : Boolean,
    settings   : Object,
    pkgSettings: Array,
  },

  components: {
    AppIcon,
    PackageFormAuth    : defineAsyncComponent(() => import('@/components/PackageFormAuth.vue')),
    PackageFormSettings: defineAsyncComponent(() => import('@/components/PackageFormSettings.vue')),
  },

  data: () => ({
    forms: {
      auth    : {use: false, show: false},
      settings: {use: false, show: false}
    }
  }),

  computed: {
    bgVariant()
    {
      if (this.enabled && this.available) {
        return 'dark';
      }
      return !this.available ? 'danger' : 'secondary';
    }
  },

  methods: {
    ...mapActions(['changePackageActivity']),
    changeActivity()
    {
      this.changePackageActivity({id: this.id, active: !this.enabled});
    },

    resolveForm(name)
    {
      this.$nextTick(() => {
        this.forms[name].show = true;
      });
    },

    showForm(name)
    {
      return this.forms[name].use ? this.resolveForm(name) : this.forms[name].use = true;
    },

    saveForm(name)
    {
      this.$refs[name].saveForm();
    }
  }
}
</script>