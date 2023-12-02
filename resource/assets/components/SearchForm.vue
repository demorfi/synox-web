<template>
  <b-form
      class="row g-3 align-items-center"
      @reset="resetForm"
      @submit.prevent="submitForm">
    <b-form-group v-bind="$attrs">
      <b-col cols="12">
        <b-input-group>
          <b-form-group
              class="form-floating position-relative"
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

  data: () => ({
    form: {
      query    : '',
      filters  : [],
      submitted: false
    },

    filters: {
      use    : false,
      show   : false,
      loading: false
    }
  }),

  methods: {
    submitForm()
    {
      this.form.submitted = true;
      this.$emit('submit', this.form.query, this.form.filters);
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