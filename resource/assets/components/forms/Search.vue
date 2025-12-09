<script setup lang="ts">
import {
  reactive,
  computed,
  inject,
  useTemplateRef,
  useAttrs,
  onBeforeMount,
  onActivated,
  defineAsyncComponent
} from 'vue';
import {useStore} from 'vuex';
import IconElement from '@/components/elements/Icon.vue';
import {prefersSchemeInjectionKey} from '@/store/keys';

const SearchFiltersForm = defineAsyncComponent(() => import('@/components/forms/SearchFilters.vue'));

const attrs = useAttrs();
const store = useStore();
const emit = defineEmits(['submit', 'abort', 'reset']);
const props = defineProps({
  progressMax: {
    type: Number,
    default: 0
  },

  progressValue: {
    type: Number,
    default: 0
  }
});

const formInitState = {
  query: '',
  profile: null,
  filters: [],
  submitted: false,
  refs: {
    filters: useTemplateRef('filters-form')
  }
};

const form = reactive({...formInitState});
const filters = reactive({
  use: false,
  show: false,
  loading: false
});

onBeforeMount(async () => await store.dispatch('profiles/getProfiles'));
onActivated(() => {
  if (form.profile !== null && store.getters["profiles/getProfileById"](form.profile) === undefined) {
    form.profile = null;
  }
});

const pScheme = inject(prefersSchemeInjectionKey);
const profiles = computed(() => store.state.profiles.profiles);
const optionsProfiles = computed(() => {
  const options = [];
  for (let profile of profiles.value) {
    options.push({value: profile.id, text: profile.id});
  }
  return options;
});

const submitForm = () => {
  form.submitted = true;
  emit('submit', form.query, form.profile, form.filters);
};

const stopSubmitForm = () => form.submitted = false;

const abortSubmittedForm = () => {
  stopSubmitForm();
  emit('abort');
};

const resetForm = () => {
  if (form.refs.filters !== null) {
    form.refs.filters.reset();
  }
  Object.assign(form, formInitState);
  emit('reset');
};

const showFilters = () => {
  if (filters.use) {
    filters.show = !filters.show;
  }
  filters.use = true;
};

defineExpose({stopSubmitForm});
</script>

<template>
  <BForm class="row g-3 align-items-center" @submit.prevent="submitForm" @reset="resetForm">
    <BFormGroup v-bind="attrs">
      <BCol cols="12">
        <BInputGroup>
          <BFormGroup class="form-floating position-relative w-50" label="Query" label-for="query" floating>
            <BFormInput v-model="form.query" :disabled="form.submitted" type="text" id="query"
                        placeholder="Enter search query" required/>
            <BProgress v-if="form.submitted" :max="progressMax" :value="progressValue"
                       variant="primary" striped animated/>
          </BFormGroup>

          <BInputGroupAppend>
            <BFormGroup label="Search profile" label-for="profile" floating>
              <BFormSelect v-model="form.profile" :options="optionsProfiles" :disabled="!profiles.length"
                           id="profile" placeholder="Search profile">
                <template #first>
                  <BFormSelectOption :value="null">None</BFormSelectOption>
                </template>
              </BFormSelect>
            </BFormGroup>

            <BButton :disabled="form.submitted" type="reset" variant="outline-secondary">Reset</BButton>

            <BButton :variant="'outline-' + pScheme.invert" aria-controls="filters"
                     :disabled="form.submitted" :class="[!filters.show && 'collapsed']"
                     :aria-expanded="filters.show ? 'true' : 'false'" @click="showFilters">
              <BSpinner v-show="filters.loading" type="grow" small/>
              Filters
              <IconElement :name="filters.show ? 'caret-up-fill' : 'caret-down-fill'"/>
            </BButton>

            <BButton v-if="!form.submitted" type="submit" variant="outline-primary">Search</BButton>

            <BButton v-else type="button" variant="outline-danger" @click="abortSubmittedForm">
              <BSpinner type="grow" small/>
              Searching
            </BButton>
          </BInputGroupAppend>
        </BInputGroup>
      </BCol>
    </BFormGroup>

    <KeepAlive>
      <Suspense v-if="filters.use" @resolve="filters.loading = false; filters.show = !filters.show"
                @pending="filters.loading = true">
        <BCollapse v-model="filters.show" id="filters" class="col-12">
          <SearchFiltersForm ref="filters-form" @selected="form.filters = $event"/>
        </BCollapse>
      </Suspense>
    </KeepAlive>

  </BForm>
</template>

<style scoped lang="scss">
@import '@/sass/mixins';

#profile {
  min-width: 8rem;
}

.progress {
  @include multiple((left, right, bottom), 1px);
  position: absolute;
  height:   6px;
}
</style>