<script setup lang="ts">
import {reactive, inject, useTemplateRef, onBeforeMount, nextTick, defineAsyncComponent} from 'vue';
import {useProfilesStore} from '@/stores/useProfilesStore';
import IconElement from '@/components/elements/Icon.vue';
import BadgeElement from '@/components/elements/Badge.vue';
import ProfileItem from '@/components/items/Profile.vue';
import {prefersSchemeInjectionKey} from '@/stores/keys';

const ProfileForm = defineAsyncComponent(() => import('@/components/forms/Profile.vue'));

const profilesStore = useProfilesStore();
const form = reactive({
  use: false,
  show: false,
  okDisabled: false,
  ref: useTemplateRef('profile-form')
});

const pScheme = inject(prefersSchemeInjectionKey);

onBeforeMount(() => profilesStore.load());

const resolveForm = () => {
  nextTick(() => form.show = true);
};

const showForm = () => form.use ? resolveForm() : form.use = true;

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
        <IconElement name="collection"/>
        <BadgeElement class="fs-9" textIndicator>{{ profilesStore.profiles.length }}</BadgeElement>
      </span>
      Profiles
    </h1>

    <BButton size="sm" :variant="'outline-' + pScheme.invert" @click="showForm()">
      <IconElement name="plus-square-fill"/>
      Add Profile
    </BButton>

    <hr class="my-3">
    <BRow class="row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xxl-6 g-4">
      <BCol v-for="profile in profilesStore.profiles" :key="profile.id">
        <ProfileItem v-bind="profile"/>
      </BCol>
    </BRow>

    <Suspense v-if="form.use" @resolve="resolveForm()">
      <BModal size="md" title="New profile" ok-title="Create" cancel-title="Close"
              v-model="form.show" :ok-disabled="form.okDisabled"
              @ok.prevent="eventForm(form.ref.save)" @cancel="form.ref.reset()" centered>
        <ProfileForm ref="profile-form"/>
      </BModal>
    </Suspense>
  </div>
</template>