<template>
  <b-card
      bg-variant="light"
      header-class="text-bg-dark rounded-0"
      body-class="rounded-0 fs-8"
      footer-class="rounded-0 fs-8"
      class="h-100 border-0 rounded-0 border-bottom">

    <template #header>
      <b-button-group>
        <b-button
            variant="outline-light"
            size="sm"
            @click="showForm('edit')">
          <AppIcon name="pencil-fill"/>
        </b-button>
      </b-button-group>
    </template>

    <template #footer>
      <b-row>
        <dl class="mb-0">
          <dt>ID</dt>
          <dd class="m-0">
            <button
                class="btn btn-link fs-8 text-start p-0 link-dark link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                @click="clipboard">
              {{ id }}
            </button>
          </dd>
        </dl>
      </b-row>
    </template>

    <template #default>
      <BListGroup flush>
        <BListGroupItem v-for="item in packages"
                        :key="item.pkgId"
                        :class="{'list-group-item-secondary': !item.pkgInfo.enabled, 'list-group-item-danger': item.pkgInfo.available === false}">
          <div class="me-auto">
            <div>{{ item.pkgInfo.name }}</div>
            <BListGroup flush>
              <BListGroupItem v-for="(filter, filterId) in item.filters"
                              :key="filterId"
                              class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                  <div class="fw-bold">{{ filter.name }}</div>
                  <span v-for="(filterValue, index) of filter.cases"
                        :key="index"
                        class="badge text-bg-secondary ms-2">
                    {{ filterValue }}
                  </span>
                </div>
                <span class="badge text-bg-secondary">{{ filter.cases.length }}</span>
              </BListGroupItem>
            </BListGroup>
          </div>
        </BListGroupItem>
      </BListGroup>
    </template>
  </b-card>

  <suspense
      v-if="forms.edit.use"
      @resolve="resolveForm('edit')">
    <b-modal
        v-model="forms.edit.show"
        title="Edit profile"
        size="md"
        centered>
      <template #footer="{ ok, cancel, hide }">
        <b-button
            :disabled="forms.edit.okDisabled"
            variant="danger"
            class="me-auto"
            @click="change($refs.edit.remove)">
          Delete
        </b-button>
        <b-button
            variant="secondary"
            @click="cancel()">
          Cancel
        </b-button>
        <b-button
            :disabled="forms.edit.okDisabled"
            variant="primary"
            @click="change($refs.edit.save)">
          Save
        </b-button>
      </template>
      <ProfileFormItem
          :id="id"
          ref="edit"/>
    </b-modal>
  </suspense>
</template>

<script>
import {defineAsyncComponent} from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import {mapGetters, mapActions} from 'vuex';

export default {
  props: {
    id    : String,
    values: Object
  },

  components: {
    AppIcon,
    ProfileFormItem: defineAsyncComponent(() => import('@/components/ProfileFormItem.vue'))
  },

  data: () => ({
    forms: {
      edit: {use: false, show: false, okDisabled: false}
    }
  }),

  computed: {
    ...mapGetters('packages', ['getPackageById', 'getFilterById']),
    packages()
    {
      const packages = [];
      for (let pkgId in this.values) {
        const pkgInfo = this.getPackageById(pkgId);
        if (pkgInfo !== undefined) {
          const filters = {};
          for (let filterId in this.values[pkgId]) {
            const filter = this.getFilterById(filterId);
            if (filter !== undefined) {
              filters[filterId] = {name: filter.name, cases: this.values[pkgId][filterId]};
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
    ...mapActions('notifications', {addNotificationInfo: 'addInfo', addNotificationError: 'addError'}),
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

    change(callable)
    {
      this.forms.edit.okDisabled = true;
      callable()
          .then(() => this.forms.edit.show = false)
          .catch(() => {})
          .finally(() => this.forms.edit.okDisabled = false);
    },

    clipboard()
    {
      navigator.clipboard.writeText(this.id).then(() => {
        this.addNotificationInfo('ID copied to clipboard');
      }, () => {
        this.addNotificationError('ID failed to copy');
      });
    }
  }
}
</script>