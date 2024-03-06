<template>
  <b-row>
    <b-col
        v-for="select in selects"
        :key="select.id"
        cols="12"
        lg="6">
      <b-card
          :header="select.name"
          header-class="border-0"
          body-class="p-0"
          class="border-0">
        <b-form-select
            v-model="select.selected"
            :options="select.options"
            select-size="4"
            multiple
            @change="selected[select.id] = $event"/>
      </b-card>
    </b-col>
  </b-row>
</template>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';

export default {
  emits: ['selected'],

  async created()
  {
    await this.getPackagesFilters();
  },

  data: () => ({
    selected: {}
  }),

  watch: {
    selected: {
      deep: true,
      handler(filters)
      {
        this.$emit('selected', filters);
      }
    }
  },

  computed: {
    ...mapState('packages', ['filters']),
    ...mapGetters('packages', ['getPackagesEnabledByType']),
    packages()
    {
      return this.getPackagesEnabledByType('Search');
    },

    selects()
    {
      let list = [];

      // Packages
      if (this.packages.length) {
        list.push({
          id      : 'packages',
          name    : 'Packages',
          selected: [],
          options : (() => {
            let options = [], types = {};
            for (let pkg of this.packages) {
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
      for (let filter of this.filters) {
        const {id, name, cases: options} = filter;
        list.push({id, name, selected: [], options});
      }

      return list;
    }
  },

  methods: {
    ...mapActions('packages', ['getPackagesFilters']),
    reset()
    {
      this.selected = {};
    }
  }
}
</script>