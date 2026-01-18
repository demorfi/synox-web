<script setup lang="ts">
import {ref, onBeforeMount} from 'vue';
import {useJournalStore} from '@/stores/useJournalStore';
import IconElement from '@/components/elements/Icon.vue';
import BadgeElement from '@/components/elements/Badge.vue';

const journalStore = useJournalStore();
const fields = ref(['message', 'date']);
const busy = ref(true);

onBeforeMount(async () => {
  await journalStore.load();
  busy.value = false;
});
</script>

<template>
  <div>
    <h1 class="display-6">
      <span class="position-relative">
        <IconElement name="journal"/>
        <BadgeElement class="fs-9" textIndicator>{{ journalStore.records.length }}</BadgeElement>
      </span>
      Journal
    </h1>

    <BButton size="sm" variant="outline-danger" @click="journalStore.clear">
      <IconElement name="trash"/>
      Clear Journal
    </BButton>

    <hr class="my-3">
    <BTable :items="journalStore.records" :fields :busy striped show-empty/>
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