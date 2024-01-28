<template>
  <b-card
      :header-class="[enabled ? 'text-bg-dark' : 'text-bg-secondary', 'rounded-0']"
      bg-variant="light"
      footer-class="rounded-0 fs-8"
      class="border-0 rounded-0 border-bottom">

    <template #header>
      <b-button-group>
        <b-button
            v-if="usesAuth"
            variant="outline-warning"
            size="sm"
            @click="$emit('auth')">
          <AppIcon name="key-fill"/>
        </b-button>
        <b-button
            :variant="enabled ? 'outline-warning' : 'outline-light'"
            :disabled="!available"
            size="sm"
            @click="$emit('status', !enabled)">
          {{ enabled ? 'Disable' : 'Enable' }}
        </b-button>
      </b-button-group>
    </template>

    <template #footer>
      <b-row>
        <dl class="col-6 mb-0">
          <dt>Type</dt>
          <dd>{{ type }} &dash; {{ subtype }}</dd>
        </dl>
        <dl class="col-6 mb-0">
          <dt>Version</dt>
          <dd>{{ version }}</dd>
        </dl>
        <dl
            v-if="requires.length"
            class="col-12 mb-0">
          <dt>Requires</dt>
          <dd>{{ requires.join(',') }}</dd>
        </dl>
      </b-row>
    </template>

    <b-card-title tag="h5">{{ name }}</b-card-title>
    <b-card-text class="text-wrap fw-lighter">{{ description }}</b-card-text>
  </b-card>
</template>

<script>
import AppIcon from '@/components/AppIcon.vue';

export default {
  components: {
    AppIcon
  },

  emits: ['status', 'auth'],
  props: {
    id         : String,
    name       : String,
    type       : String,
    subtype    : String,
    version    : String,
    description: String,
    enabled    : Boolean,
    available  : Boolean,
    requires   : Array,
    usesAuth   : Boolean
  }
}
</script>