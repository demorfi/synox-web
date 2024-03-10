<template>
  <b-form
      class="row g-3 align-items-center"
      @reset="resetForm"
      @submit.prevent="submitForm">
    <b-form-group v-bind="$attrs">
      <b-col cols="12">
        <b-input-group>
          <b-form-group
              class="form-floating position-relative w-50"
              label="Query"
              label-for="query"
              floating>
            <b-form-input
                v-model="form.query"
                :disabled="form.submitted"
                type="text"
                id="query"
                placeholder="Enter search query"
                required/>
            <b-progress
                v-if="form.submitted"
                :max="progressMax"
                :value="progressValue"
                variant="primary"
                striped
                animated></b-progress>
          </b-form-group>

          <b-input-group-append>
            <b-form-group
                label="Search profile"
                label-for="profile"
                floating>
              <b-form-select
                  v-model="form.profile"
                  :options="optionsProfiles"
                  :disabled="!profiles.length"
                  id="profile"
                  placeholder="Search profile">
                <template #first>
                  <b-form-select-option
                      :value="null">
                    None
                  </b-form-select-option>
                </template>
              </b-form-select>
            </b-form-group>

            <b-button
                :disabled="form.submitted"
                type="reset"
                variant="outline-secondary">
              Reset
            </b-button>

            <b-button
                :disabled="form.submitted"
                :class="[!filters.show && 'collapsed']"
                :aria-expanded="filters.show ? 'true' : 'false'"
                aria-controls="filters"
                variant="outline-dark"
                @click="showFilters">
              <b-spinner
                  v-show="filters.loading"
                  type="grow"
                  small/>
              Filters
              <AppIcon :name="filters.show ? 'caret-up-fill' : 'caret-down-fill'"/>
            </b-button>

            <b-button
                v-if="!form.submitted"
                type="submit"
                variant="outline-primary">
              Search
            </b-button>

            <b-button
                v-else
                type="button"
                variant="outline-danger"
                @click="abortSubmittedForm">
              <b-spinner
                  type="grow"
                  small/>
              Searching
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </b-col>
    </b-form-group>

    <keep-alive>
      <suspense
          v-if="filters.use"
          @resolve="filters.loading = false; filters.show = !filters.show"
          @pending="filters.loading = true">
        <b-collapse
            v-model="filters.show"
            id="filters"
            class="col-12">
          <SearchFormFilters ref="searchFilters" @selected="form.filters = $event"/>
        </b-collapse>
      </suspense>
    </keep-alive>

  </b-form>
</template>

<script>
import {defineAsyncComponent} from 'vue';
import {mapState, mapActions, mapGetters} from 'vuex';
import AppIcon from '@/components/AppIcon.vue';

export default {
  emits: ['submit', 'abort', 'reset'],
  props: {
    progressMax: {
      type   : Number,
      default: 0
    },

    progressValue: {
      type   : Number,
      default: 0
    }
  },

  components: {
    AppIcon,
    SearchFormFilters: defineAsyncComponent(() => import('@/components/SearchFormFilters.vue'))
  },

  async created()
  {
    await this.getProfiles();
  },

  activated()
  {
    if (this.form.profile !== null && this.getProfileById(this.form.profile) === undefined) {
      this.form.profile = null;
    }
  },

  data: () => ({
    form: {
      query    : '',
      profile  : null,
      filters  : [],
      submitted: false
    },

    filters: {
      use    : false,
      show   : false,
      loading: false
    }
  }),

  computed: {
    ...mapState('profiles', ['profiles']),
    ...mapGetters('profiles', ['getProfileById']),
    optionsProfiles()
    {
      const options = [];
      for (let profile of this.profiles) {
        options.push({value: profile.id, text: profile.id});
      }
      return options;
    }
  },

  methods: {
    ...mapActions('profiles', ['getProfiles']),
    submitForm()
    {
      this.form.submitted = true;
      this.$emit('submit', this.form.query, this.form.profile, this.form.filters);
    },

    stopSubmitForm()
    {
      this.form.submitted = false;
    },

    abortSubmittedForm()
    {
      this.stopSubmitForm();
      this.$emit('abort');
    },

    resetForm()
    {
      this.$refs.searchFilters.reset();
      this.form = this.$options.data().form;
      this.$emit('reset');
    },

    showFilters()
    {
      if (this.filters.use) {
        this.filters.show = !this.filters.show;
      }
      this.filters.use = true;
    }
  }
}
</script>

<style lang="scss" scoped>
@import '@/sass/mixins';

.progress {
  @include multiple((left, right, bottom), 1px);
  position: absolute;
  height: 6px;
}
</style>