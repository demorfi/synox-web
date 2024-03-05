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
            v-bind="pkg"/>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';
import PackageItem from '@/components/PackageItem.vue';
import AppIcon from '@/components/AppIcon.vue';

const {mapState} = createNamespacedHelpers('packages');

export default {
  name      : 'Packages',
  components: {
    AppIcon,
    PackageItem
  },

  computed: {
    ...mapState(['packages'])
  }
}
</script>