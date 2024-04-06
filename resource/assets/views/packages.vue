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

    <b-button
        variant="outline-dark"
        size="sm"
        @click="showForm('add')">
      <AppIcon name="plus-square-fill"/>
      Add Package
    </b-button>

    <hr class="my-3">
    <b-row class="row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xxl-6 g-4">
      <b-col
          v-for="pkg in packages"
          :key="pkg.id">
        <PackageItem
            v-bind="pkg"/>
      </b-col>
    </b-row>

    <suspense
        v-if="forms.add.use"
        @resolve="resolveForm('add')">
      <b-modal
          v-model="forms.add.show"
          :ok-disabled="forms.add.okDisabled"
          title="Add package"
          size="md"
          ok-title="Upload"
          cancel-title="Close"
          centered
          @ok.prevent="upload"
          @cancel="$refs.add.reset()">
        <PackageFormItem
            ref="add"/>
      </b-modal>
    </suspense>
  </div>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';
import PackageItem from '@/components/PackageItem.vue';
import AppIcon from '@/components/AppIcon.vue';
import {defineAsyncComponent} from "vue";

const {mapState, mapActions} = createNamespacedHelpers('packages');

export default {
  name      : 'Packages',
  components: {
    AppIcon,
    PackageItem,
    PackageFormItem: defineAsyncComponent(() => import('@/components/PackageFormItem.vue'))
  },

  data: () => ({
    forms: {
      add: {use: false, show: false, okDisabled: false}
    }
  }),

  computed: {
    ...mapState(['packages'])
  },

  methods: {
    ...mapActions(['getPackages']),
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

    upload()
    {
      this.forms.add.okDisabled = true;
      this.$refs.add.upload()
          .then(() => {
            this.forms.add.show = false;
            this.$refs.add.reset();
          })
          .catch(() => {})
          .finally(() => this.forms.add.okDisabled = false);
    }
  }
}
</script>