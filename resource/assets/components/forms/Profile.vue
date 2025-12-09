<script setup lang="ts">
import {ref, computed, watchEffect} from 'vue';
import {useStore} from 'vuex';

const store = useStore();
const props = defineProps({id: String});
const selected = ref({id: '', packages: {}});

const filtersState = computed(() => store.state.packages.filters);
const selectPackageState = ref(null);
const profile = ref(null);

const optionsSelectPackage = computed(() => {
  const packages = store.getters["packages/getPackagesByType"]('Search');
  const options = [];
  for (let pkg of packages) {
    if (!(pkg.id in selected.value.packages)) {
      options.push({value: pkg.id, text: pkg.name});
    }
  }
  return options;
});

const selectedPackages = computed(() => {
  const packages = [];
  for (let [pkgId] of Object.entries(selected.value.packages)) {
    const pkgInfo = store.getters["packages/getPackageById"](pkgId);
    if (pkgInfo !== undefined) {
      const filters = {};

      const addToFilter = (id, name, values) => {
        const options = [];
        for (let value of values) {
          options.push({text: value, value});
        }
        filters[id] = {name, options};
      };

      if (pkgInfo.onlyAllowed !== undefined && Object.keys(pkgInfo.onlyAllowed).length) {
        for (let [filterId, filterValues] of Object.entries(pkgInfo.onlyAllowed)) {
          const filter = store.getters["packages/getFilterById"](filterId);
          if (filter !== undefined) {
            addToFilter(filterId, filter.name, filterValues);
          }
        }
      } else {
        for (let [filterId, filter] of Object.entries(filtersState.value)) {
          addToFilter(filterId, filter.name, filter.cases);
        }
      }

      if (Object.keys(filters).length) {
        packages.push({pkgId, pkgInfo, filters});
      }
    }
  }
  return packages;
});

const selectPackage = (pkgId) => {
  selectPackageState.value = null;
  selected.value.packages[pkgId] = {};
  const packages = (profile.value !== null && pkgId in profile.value.values) ? profile.value.values[pkgId] : {};
  for (let filter of filtersState.value) {
    selected.value.packages[pkgId][filter.id] = (filter.id in packages) ? [...packages[filter.id]] : [];
  }
}

watchEffect(() => {
  if (props.id) {
    profile.value = store.getters['profiles/getProfileById'](props.id) ?? null;
    if (profile.value !== null) {
      selected.value.id = profile.value.id;
      for (let pkgId of Object.keys(profile.value.values)) {
        selectPackage(pkgId);
      }
    }
  }
});

const reset = () => selected.value = {id: '', packages: {}};

const validate = () => {
  const packages = Object.entries(selected.value.packages);
  if (!packages.length) {
    selectPackageState.value = false;
    return false;
  }

  for (let [, filtersState] of packages) {
    for (let [, filter] of Object.entries(filtersState)) {
      if (!filter.length) {
        selectPackageState.value = false;
        return false;
      }
    }
  }
  return true;
};

const save = () => {
  if (validate()) {
    return props.id !== undefined
        ? store.dispatch('profiles/updateProfile', selected.value)
        : store.dispatch('profiles/createProfile', selected.value);
  }
  return new Promise((resolve, reject) => reject());
}

const remove = () => store.dispatch('profiles/removeProfile', selected.value);

defineExpose({reset, validate, save, remove});
</script>

<template>
  <BForm @submit.prevent="save">
    <BFormGroup class="mb-3" label="*ID" label-for="profileId" floating>
      <BFormInput type="text" id="profileId" placeholder="Enter profile id *(Optional)"
                  v-model="selected.id" :disabled="!!id"/>
    </BFormGroup>

    <BFormGroup class="mb-3" label="Select the package" label-for="selectPackage" floating>
      <BFormSelect id="selectPackage" placeholder="Select the package" :model-value="null"
                   :options="optionsSelectPackage" :state="selectPackageState" @change="selectPackage($event)">
        <template #first>
          <BFormSelectOption :value="null" disabled>Select the package for added</BFormSelectOption>
        </template>
      </BFormSelect>
    </BFormGroup>

    <BListGroup flush>
      <BListGroupItem v-for="item in selectedPackages" :key="item.pkgId"
                      :class="{'list-group-item-secondary': !item.pkgInfo.enabled, 'list-group-item-danger': !item.pkgInfo.available}">
        <div class="me-auto">
          <div>{{ item.pkgInfo.name }}</div>
          <BListGroup flush>
            <BListGroupItem v-for="(filter, filterId) in item.filters" :key="filterId"
                            class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">{{ filter.name }}</div>
                <BFormCheckbox v-for="option in filter.options" :key="option.value" :value="option.value"
                               v-model="selected.packages[item.pkgId][filterId]" size="sm"
                               button-variant="outline-secondary" inline button>
                  {{ option.text }}
                </BFormCheckbox>
              </div>
            </BListGroupItem>
          </BListGroup>
        </div>
      </BListGroupItem>
    </BListGroup>
  </BForm>
</template>

<style scoped>
form:deep(.form-check-inline) {
  margin: .5rem 0 0 .5rem;
}
</style>