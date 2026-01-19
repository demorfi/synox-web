<script setup lang="ts">
import {reactive, computed, inject, useTemplateRef, nextTick, defineAsyncComponent} from 'vue';
import {useFiltersStore} from '@/stores/useFiltersStore';
import {usePackagesStore} from '@/stores/usePackagesStore';
import {useNotificationsStore} from '@/stores/useNotificationsStore';
import IconElement from '@/components/elements/Icon.vue';
import {prefersSchemeInjectionKey} from '@/stores/keys';

const ProfileForm = defineAsyncComponent(() => import('@/components/forms/Profile.vue'));

const pkgStore = usePackagesStore();
const filtersStore = useFiltersStore();
const notificationsStore = useNotificationsStore();
const props = defineProps({id: String, values: Object});
const form = reactive({
  use: false,
  show: false,
  okDisabled: false,
  ref: useTemplateRef('profile-form')
});

const pScheme = inject(prefersSchemeInjectionKey);
const selectedPackages = computed(() => {
  const packages = [];
  for (let [pkgId, pkgValues] of Object.entries(props.values)) {
    const pkgInfo = pkgStore.getById(pkgId);
    if (pkgInfo !== undefined) {
      const filters = {};
      for (let [filterId, filterValues] of Object.entries(pkgValues)) {
        const filter = filtersStore.getById(filterId);
        if (filter !== undefined) {
          filters[filterId] = {name: filter.name, cases: filterValues};
        }
      }
      if (Object.keys(filters).length) {
        packages.push({pkgId, pkgInfo, filters});
      }
    }
  }
  return packages;
});

const resolveForm = () => {
  nextTick(() => form.show = true);
};

const showForm = () => form.use ? resolveForm() : form.use = true;

const eventForm = (callable) => {
  form.okDisabled = true;
  callable()
      .then(() => form.show = false)
      .catch(() => {
      })
      .finally(() => form.okDisabled = false);
};

const clipboard = () => {
  if (!navigator.clipboard) {
    notificationsStore.addError('ID failed to copy: Unsecure context (HTTP)');
    return;
  }
  navigator.clipboard?.writeText(props.id).then(() => {
    notificationsStore.addInfo('ID copied to clipboard');
  }, (error) => {
    notificationsStore.addError('ID failed to copy');
    console.error('Clipboard error:', error);
  });
}
</script>

<template>
  <BCard class="h-100 border-0 rounded-0 border-bottom" :bg-variant="pScheme.color"
         header-class="text-bg-dark rounded-0"
         body-class="rounded-0 fs-8" footer-class="rounded-0 fs-8">

    <template #header>
      <BButtonGroup>
        <BButton size="sm" variant="outline-light" @click="showForm()">
          <IconElement name="pencil-fill"/>
        </BButton>
      </BButtonGroup>
    </template>

    <template #footer>
      <BRow>
        <dl class="mb-0">
          <dt>ID</dt>
          <dd class="m-0">
            <button
                :class="'link-' + pScheme.invert"
                class="btn btn-link fs-8 text-start p-0 link-offset-2 link-underline-opacity-25 border-0 link-underline-opacity-100-hover"
                @click="clipboard">{{ id }}
            </button>
          </dd>
        </dl>
      </BRow>
    </template>

    <template #default>
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
                  <span v-for="(filterValue, index) of filter.cases" :key="index" class="badge text-bg-secondary ms-2">
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
  </BCard>

  <Suspense v-if="form.use" @resolve="resolveForm()">
    <BModal size="md" title="Edit profile" v-model="form.show" centered>
      <template #footer="{ ok, cancel, hide }">
        <BButton class="me-auto" variant="danger" :disabled="form.okDisabled" @click="eventForm(form.ref.remove)">
          Delete
        </BButton>
        <BButton variant="secondary" @click="cancel">Cancel</BButton>
        <BButton variant="primary" :disabled="form.okDisabled" @click="eventForm(form.ref.save)">Save</BButton>
      </template>
      <ProfileForm ref="profile-form" :id/>
    </BModal>
  </Suspense>
</template>