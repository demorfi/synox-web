<template>
  <div>
    <h1 class="display-6">
      <span class="position-relative">
        <AppIcon name="collection"/>
        <b-badge
            variant="dark"
            class="fs-9"
            pill
            textIndicator>{{ profiles.length }}</b-badge>
      </span>
      {{ $options.name }}
    </h1>

    <b-button
        variant="outline-dark"
        size="sm"
        @click="showForm('add')">
      <AppIcon name="plus-square-fill"/>
      Add Profile
    </b-button>

    <hr class="my-3">
    <b-row class="row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xxl-6 g-4">
      <b-col
          v-for="profile in profiles"
          :key="profile.id">
        <ProfileItem
            v-bind="profile"/>
      </b-col>
    </b-row>

    <suspense
        v-if="forms.add.use"
        @resolve="resolveForm('add')">
      <b-modal
          v-model="forms.add.show"
          :ok-disabled="forms.add.okDisabled"
          title="New profile"
          size="md"
          ok-title="Create"
          cancel-title="Close"
          centered
          @ok.prevent="create"
          @cancel="$refs.add.reset()">
        <ProfileFormItem
            ref="add"/>
      </b-modal>
    </suspense>
  </div>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';
import {defineAsyncComponent} from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import ProfileItem from '@/components/ProfileItem.vue';

const {mapState, mapActions} = createNamespacedHelpers('profiles');

export default {
  name      : 'Profiles',
  components: {
    AppIcon,
    ProfileItem,
    ProfileFormItem: defineAsyncComponent(() => import('@/components/ProfileFormItem.vue'))
  },

  async created()
  {
    await this.getProfiles();
  },

  data: () => ({
    forms: {
      add: {use: false, show: false, okDisabled: false}
    }
  }),

  computed: {
    ...mapState(['profiles'])
  },

  methods: {
    ...mapActions(['getProfiles']),
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

    create()
    {
      this.forms.add.okDisabled = true;
      this.$refs.add.save()
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

<style lang="scss" scoped>
.btn.btn-link {
  border: none;
}
</style>