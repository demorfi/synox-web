<script setup lang="ts">
import {reactive, computed, inject, nextTick, useTemplateRef, defineAsyncComponent} from 'vue';
import {usePackagesStore} from '@/stores/usePackagesStore';
import IconElement from '@/components/elements/Icon.vue';
import {prefersSchemeInjectionKey} from '@/stores/keys';

const PackageAuthForm = defineAsyncComponent(() => import('@/components/forms/PackageAuth.vue'));
const PackageSettingsForm = defineAsyncComponent(() => import('@/components/forms/PackageSettings.vue'));

const pkgStore = usePackagesStore();
const props = defineProps({
  id: String,
  name: String,
  type: String,
  subtype: String,
  version: String,
  description: String,
  enabled: Boolean,
  available: Boolean,
  onlyAllowed: Object,
  requires: Array,
  usesAuth: Boolean,
  settings: Object,
  pkgSettings: Array
});

const forms = reactive({
  auth: {
    use: false,
    show: false,
    ref: useTemplateRef('auth-form')
  },
  settings: {
    use: false,
    show: false,
    ref: useTemplateRef('settings-form')
  }
});

const pScheme = inject(prefersSchemeInjectionKey);
const bgVariant = computed(() => {
  if (props.enabled && props.available) {
    return 'text-bg-dark';
  }
  return !props.available ? 'text-bg-danger' : 'text-bg-secondary';
});

const changeActivity = () => pkgStore.changeActivity(props.id, !props.enabled);

const resolveForm = (name) => {
  nextTick(() => forms[name].show = true);
};

const showForm = (name) => forms[name].use ? resolveForm(name) : forms[name].use = true;

const saveForm = (name) => forms[name].ref.saveForm();

</script>

<template>
  <BCard :header-class="[bgVariant, 'rounded-0']" :bg-variant="pScheme.color" footer-class="rounded-0 fs-8"
         class="h-100 border-0 rounded-0 border-bottom">

    <template #header>
      <BButtonGroup>
        <BButton v-if="usesAuth" variant="outline-warning" size="sm" @click="showForm('auth')">
          <IconElement name="key-fill"/>
        </BButton>
        <BButton v-if="pkgSettings.length" :variant="enabled ? 'outline-warning' : 'outline-light'"
                 size="sm" @click="showForm('settings')">
          <IconElement name="gear"/>
        </BButton>
        <BButton :variant="enabled ? 'outline-warning' : 'outline-light'" size="sm" @click="changeActivity">
          {{ enabled ? 'Disable' : 'Enable' }}
        </BButton>
      </BButtonGroup>
    </template>

    <template #footer>
      <BRow>
        <dl class="col-6 mb-0">
          <dt>Type</dt>
          <dd>{{ type }} &dash; {{ subtype }}</dd>
        </dl>
        <dl class="col-6 mb-0">
          <dt>Version</dt>
          <dd>{{ version }}</dd>
        </dl>
        <dl v-if="requires.length" class="col-12 mb-0">
          <dt>Requires</dt>
          <dd>{{ requires.join(', ') }}</dd>
        </dl>
      </BRow>
    </template>

    <BCardTitle tag="h5">{{ name }}</BCardTitle>
    <BCardText class="text-wrap fw-lighter">{{ description }}</BCardText>
  </BCard>

  <Suspense v-if="forms.auth.use" @resolve="resolveForm('auth')">
    <BModal v-model="forms.auth.show" :title="name" size="sm" ok-title="Save" cancel-title="Close"
            @ok="saveForm('auth')" centered>
      <PackageAuthForm :id ref="auth-form"/>
    </BModal>
  </Suspense>

  <Suspense v-if="forms.settings.use" @resolve="resolveForm('settings')">
    <BModal v-model="forms.settings.show" :title="name" size="md" ok-title="Save" cancel-title="Close"
            @ok="saveForm('settings')" centered>
      <PackageSettingsForm :id ref="settings-form"/>
    </BModal>
  </Suspense>
</template>