<script setup lang="ts">
import {ref, computed, onBeforeMount} from 'vue';
import {useStore} from 'vuex';
import IconElement from '@/components/elements/Icon.vue';
import BadgeElement from '@/components/elements/Badge.vue';

const store = useStore();
const fields = ref(['message', 'date']);
const busy = ref(true);
const journalState = computed(() => store.state.journal.journal);

onBeforeMount(async () => {
  await store.dispatch('journal/getJournal');
  busy.value = false;
});

const clearJournal = () => store.dispatch('journal/clearJournal');
</script>

<template>
  <div>
    <h1 class="display-6">
      <span class="position-relative">
        <IconElement name="journal"/>
        <BadgeElement class="fs-9" textIndicator>{{ journalState.length }}</BadgeElement>
      </span>
      Journal
    </h1>

    <BButton size="sm" variant="outline-danger" @click="clearJournal">
      <IconElement name="trash"/>
      Clear Journal
    </BButton>

    <hr class="my-3">
    <BTable :items="journalState" :fields :busy striped show-empty/>
  </div>
</template>

<style scoped>
table:deep(tbody) {
  border-top: 2px solid currentcolor;
}

table:deep(.b-table-busy-slot + .b-table-empty-slot) {
  display: none;
}

table:deep(.b-table-busy-slot .d-flex) {
  padding: .5rem 0;
}
</style>