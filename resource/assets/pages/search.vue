<script setup lang="ts">
import {ref, computed, watch, useTemplateRef} from 'vue';
import {useStore} from 'vuex';
import SearchResultItem from '@/components/items/SearchResult.vue';
import IconElement from '@/components/elements/Icon.vue';
import SearchForm from '@/components/forms/Search.vue';

const store = useStore();
const searching = ref(false);
const firstSearch = ref(true);
const searchFormRef = useTemplateRef('search-form');

const connected = computed(() => store.state.search.connected);
const payloads = computed(() => store.state.search.payloads);
const limitPayloads = computed(() => store.state.search.limitPayloads);
const progressToLimit = computed(() => store.state.search.progressToLimit);
const packages = computed(() => store.getters["packages/getPackagesEnabledByType"]('Search'));

watch(searching, (status) => {
  if (!status) {
    searchFormRef.value.stopSubmitForm();
  }
});

watch(connected, (status) => {
  if (!status) {
    searching.value = false;
  }
});

const search = (query, profile, filters) => {
  searching.value = true;
  store.dispatch('search/connection', {query, profile, filters})
      .then(() => {
        if (!searching.value) {
          throw new Error('Search terminated!');
        }

        resetResults();
        firstSearch.value = false;
      })
      .catch(() => abortSearch());
};

const abortSearch = () => {
  searching.value = false;
  store.dispatch('search/disconnection');
};

const resetResults = () => store.commit('search/clearPayloads');
</script>

<template>
  <div>
    <h1 class="display-6">
      <IconElement name="search"/>
      Search
    </h1>

    <SearchForm :disabled="!packages.length" :progress-max="limitPayloads" :progress-value="progressToLimit"
                ref="search-form" @submit="search" @abort="abortSearch" @reset="resetResults"/>

    <hr class="my-3">
    <b-row cols="1" class="g-4">

      <b-col v-if="!packages.length">
        <b-alert :model-value="true" variant="warning">
          Please enable at least one
          <b-link to="packages">search package!</b-link>
        </b-alert>
      </b-col>

      <template v-else>
        <b-col v-show="!payloads.length && firstSearch">
          <b-alert :model-value="true" variant="info">
            Use a search query to get results
          </b-alert>
        </b-col>

        <b-col v-for="(item, index) in payloads" :key="index">
          <SearchResultItem v-bind="item"/>
        </b-col>

        <b-col v-show="connected" class="text-center">
          <b-spinner/>
        </b-col>
      </template>
    </b-row>
  </div>
</template>