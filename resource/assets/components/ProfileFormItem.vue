<template>
  <b-form>
    <b-form-group
        class="mb-3"
        label="*ID"
        label-for="profileId"
        floating>
      <b-form-input
          v-model="selected.id"
          :disabled="!!id"
          type="text"
          id="profileId"
          placeholder="Enter profile id *(Optional)"/>
    </b-form-group>

    <b-form-group
        class="mb-3"
        label="Select the package"
        label-for="selectPackage"
        floating>
      <b-form-select
          :model-value="null"
          :options="optionsSelectPackage"
          id="selectPackage"
          placeholder="Select the package"
          @change="selectPackage($event)">
        <template #first>
          <b-form-select-option
              :value="null"
              disabled>
            Select the package for added
          </b-form-select-option>
        </template>
      </b-form-select>
    </b-form-group>

    <BListGroup flush>
      <BListGroupItem v-for="item in packages"
                      :key="item.pkgId"
                      :class="{'list-group-item-secondary': !item.pkgInfo.enabled, 'list-group-item-danger': !item.pkgInfo.available}">
        <div class="me-auto">
          <div>{{ item.pkgInfo.name }}</div>
          <BListGroup flush>
            <BListGroupItem v-for="(filter, filterId) in item.filters"
                            :key="filterId"
                            class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">{{ filter.name }}</div>
                <BFormCheckbox
                    v-for="option in filter.options"
                    v-model="selected.packages[item.pkgId][filterId]"
                    :key="option.value"
                    :value="option.value"
                    button-variant="outline-secondary"
                    size="sm"
                    inline
                    button>
                  {{ option.text }}
                </BFormCheckbox>
              </div>
            </BListGroupItem>
          </BListGroup>
        </div>
      </BListGroupItem>
    </BListGroup>
  </b-form>
</template>

<script>
import {mapGetters, mapActions, mapState} from 'vuex';

export default {
  props: {
    id: String
  },

  created()
  {
    this.load();
  },

  data: () => ({
    profile : null,
    selected: {
      id      : '',
      packages: {}
    }
  }),

  computed: {
    ...mapState('packages', ['filters']),
    ...mapGetters('packages', ['getPackagesByType', 'getPackageById', 'getFilterById']),
    ...mapGetters('profiles', ['getProfileById']),
    optionsSelectPackage()
    {
      const packages = this.getPackagesByType('Search');
      const options = [];
      for (let pkg of packages) {
        if (!(pkg.id in this.selected.packages)) {
          options.push({value: pkg.id, text: pkg.name});
        }
      }
      return options;
    },

    packages()
    {
      const packages = [];
      for (let pkgId in this.selected.packages) {
        const pkgInfo = this.getPackageById(pkgId);
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
            for (let filterId in pkgInfo.onlyAllowed) {
              const filter = this.getFilterById(filterId);
              if (filter !== undefined) {
                addToFilter(filterId, filter.name, pkgInfo.onlyAllowed[filterId]);
              }
            }
          } else {
            for (let filterId in this.filters) {
              addToFilter(filterId, this.filters[filterId].name, this.filters[filterId].cases);
            }
          }

          if (Object.keys(filters).length) {
            packages.push({pkgId, pkgInfo, filters});
          }
        }
      }
      return packages;
    }
  },

  methods: {
    ...mapActions('profiles', ['createProfile', 'updateProfile', 'removeProfile']),
    selectPackage(pkgId)
    {
      this.selected.packages[pkgId] = {};
      const packages = (this.profile !== null && pkgId in this.profile.values) ? this.profile.values[pkgId] : {};
      for (let filter of this.filters) {
        this.selected.packages[pkgId][filter.id] = (filter.id in packages) ? [...packages[filter.id]] : [];
      }
    },

    load()
    {
      if (this.id !== undefined) {
        this.profile = this.getProfileById(this.id) ?? null;
        if (this.profile !== null) {
          this.selected.id = this.profile.id;
          for (let pkgId in this.profile.values) {
            this.selectPackage(pkgId);
          }
        }
      }
    },

    reset()
    {
      this.selected = this.$options.data().selected;
    },

    save()
    {
      return this.id !== undefined ? this.updateProfile(this.selected) : this.createProfile(this.selected);
    },

    remove()
    {
      return this.removeProfile(this.selected);
    }
  }
}
</script>

<style lang="scss" scoped>
.form-check-inline {
  margin: .5rem 0 0 .5rem;
}
</style>