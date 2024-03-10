<template>
  <div>
    <h1 class="display-6">
      <AppIcon name="search"/>
      {{ $options.name }}
    </h1>

    <SearchForm
        :disabled="!packages.length"
        :progress-max="limitPayloads"
        :progress-value="progressToLimit"
        ref="searchForm"
        @submit="search"
        @abort="abortSearch"
        @reset="resetResults"/>

    <hr class="my-3">
    <b-row
        cols="1"
        class="g-4">

      <b-col v-if="!packages.length">
        <b-alert
            :model-value="true"
            variant="warning">
          Please enable at least one
          <b-link to="packages">search package!</b-link>
        </b-alert>
      </b-col>

      <template v-else>
        <b-col v-show="!payloads.length && firstSearch">
          <b-alert
              :model-value="true"
              variant="info">
            Use a search query to get results
          </b-alert>
        </b-col>

        <b-col
            v-for="(item, index) in payloads"
            :key="index">
          <SearchResultItem v-bind="item"/>
        </b-col>

        <b-col
            v-show="connected"
            class="text-center">
          <b-spinner/>
        </b-col>
      </template>
    </b-row>
  </div>
</template>

<script>
import {mapGetters, mapActions, mapState, mapMutations} from 'vuex';
import SearchResultItem from '@/components/SearchResultItem.vue';
import SearchForm from '@/components/SearchForm.vue';
import AppIcon from '@/components/AppIcon.vue';

export default {
  name      : 'Search',
  components: {
    AppIcon,
    SearchForm,
    SearchResultItem
  },

  data: () => ({
    searching  : false,
    firstSearch: true
  }),

  computed: {
    ...mapState('search', ['connected', 'payloads', 'limitPayloads', 'progressToLimit']),
    ...mapGetters('packages', ['getPackagesEnabledByType']),
    packages()
    {
      return this.getPackagesEnabledByType('Search');
    }
  },

  watch: {
    searching(status)
    {
      !status && this.$refs.searchForm.stopSubmitForm();
    },

    connected(status)
    {
      if (!status) {
        this.searching = false;
      }
    }
  },

  methods: {
    ...mapActions('search', ['connection', 'disconnection']),
    ...mapMutations('search', ['clearPayloads']),
    search(query, profile, filters)
    {
      this.searching = true;
      this.connection({query, profile, filters})
          .then(() => {
            if (!this.searching) {
              throw new Error('Search terminated!');
            }

            this.resetResults();
            this.firstSearch = false;
          })
          .catch(() => this.abortSearch());
    },

    abortSearch()
    {
      this.searching = false;
      this.disconnection();
    },

    resetResults()
    {
      this.clearPayloads();
    }
  }
}
</script>