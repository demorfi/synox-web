<script setup lang="ts">
import {reactive, computed, inject, nextTick, defineAsyncComponent, useTemplateRef} from 'vue';
import {usePackagesStore} from '@/stores/usePackagesStore';
import IconElement from '@/components/elements/Icon.vue';
import BadgeElement from '@/components/elements/Badge.vue';
import PackageItem from '@/components/items/Package.vue';
import {prefersSchemeInjectionKey} from '@/stores/keys';

const PackageAddForm = defineAsyncComponent(() => import('@/components/forms/PackageAdd.vue'));

const pkgStore = usePackagesStore();
const form = reactive({
  use: false,
  show: false,
  okDisabled: false,
  ref: useTemplateRef('package-add-form')
});

const pScheme = inject(prefersSchemeInjectionKey);
const packages = computed(() => pkgStore.packages);

const resolveForm = () => {
  nextTick(() => form.show = true);
};

const showForm = (name) => form.use ? resolveForm(name) : form.use = true;

const eventForm = (callable) => {
  form.okDisabled = true;
  callable()
      .then(() => {
        form.show = false;
        form.ref.reset();
      })
      .catch(() => {
      })
      .finally(() => form.okDisabled = false);
};
</script>

<template>
  <div>
    <h1 class="display-6">
      <span class="position-relative">
        <IconElement name="box-seam"/>
        <BadgeElement class="fs-9" textIndicator>{{ packages.length }}</BadgeElement>
      </span>
      Packages
    </h1>

    <BButton size="sm" :variant="'outline-' + pScheme.invert" @click="showForm()">
      <IconElement name="plus-square-fill"/>
      Add Package
    </BButton>

    <hr class="my-3">
    <BRow class="row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xxl-6 g-4">
      <BCol v-for="pkg in packages" key="pkg.id">
        <PackageItem v-bind="pkg"/>
      </BCol>
    </BRow>

    <Suspense v-if="form.use" @resolve="resolveForm()">
      <BModal size="md" title="Add package" ok-title="Upload" cancel-title="Close"
              v-model="form.show" :ok-disabled="form.okDisabled"
              @ok.prevent="eventForm(form.ref.upload)" @cancel="form.ref.reset()" centered>
        <PackageAddForm ref="package-add-form"/>
      </BModal>
    </Suspense>
  </div>
</template>