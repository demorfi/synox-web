<script setup lang="ts">
import {ref, computed, watch} from 'vue';
import {useFiltersStore} from '@/stores/useFiltersStore';
import {usePackagesStore} from '@/stores/usePackagesStore';

const pkgStore = usePackagesStore();
const filtersStore = useFiltersStore();
const emit = defineEmits(['selected']);
const selected = ref({});

const filters = computed(() => filtersStore.filters);
const packages = computed(() => pkgStore.getEnabledByType('Search'));
const selects = computed(() => {
  const list = [];

  // Packages
  if (packages.value.length) {
    list.push({
      id: 'packages',
      name: 'Packages',
      selected: [],
      options: (() => {
        const options = [], types = {};
        for (let pkg of packages.value) {
          if (!(pkg.subtype in types)) {
            types[pkg.subtype] = options.length;
            options[options.length] = {label: pkg.subtype + 's', options: []};
          }

          options[types[pkg.subtype]].options.push({value: pkg.id, text: pkg.name});
        }
        return options;
      })()
    });
  }

  // Other filters
  for (let filter of filters.value) {
    const {id, name, cases: options} = filter;
    list.push({id, name, selected: [], options});
  }
  return list;
});

watch(selected, (filters) => emit('selected', filters), {deep: true});

const reset = () => selected.value = {};

defineExpose({reset});
</script>

<template>
  <BRow>
    <BCol v-for="select in selects" :key="select.id" cols="12" lg="6">
      <BCard :header="select.name" header-class="border-0" body-class="p-0" class="border-0">
        <BFormSelect v-model="select.selected" :options="select.options" select-size="4"
                     @change="selected[select.id] = $event" multiple/>
      </BCard>
    </BCol>
  </BRow>
</template>