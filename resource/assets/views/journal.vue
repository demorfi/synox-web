<template>
  <div>
    <h1 class="display-6">
    <span class="position-relative">
      <AppIcon name="journal"/>
      <b-badge
          variant="dark"
          class="fs-9"
          pill
          textIndicator>
        {{ journal.length }}
      </b-badge>
    </span>
      {{ $options.name }}
    </h1>

    <b-button
        variant="outline-danger"
        size="sm"
        @click="clearJournal">
      <AppIcon name="trash"/>
      Clear Journal
    </b-button>

    <hr class="my-3">
    <b-table :items="journal"
             :fields="fields"
             striped
             show-empty>
    </b-table>
  </div>
</template>

<script>
import {createNamespacedHelpers} from 'vuex';
import AppIcon from '@/components/AppIcon.vue';

const {mapState, mapActions} = createNamespacedHelpers('journal');

export default {
  name      : 'Journal',
  components: {
    AppIcon
  },

  async created()
  {
    await this.getJournal();
  },

  data: () => ({
    fields: ['message', 'date']
  }),

  computed: {
    ...mapState(['journal'])
  },

  methods: {
    ...mapActions(['getJournal', 'clearJournal'])
  }
}
</script>

<style scoped>
table:deep(tbody)
{
  border-top : 2px solid currentcolor;
}
</style>