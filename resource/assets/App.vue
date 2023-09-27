<template>
  <div class="container-fluid">
    <div class="row">
      <TheSidebar/>
      <div class="col-sm p-3 min-vh-100">
        <div class="card border-0">
          <TheNotifications/>
          <router-view
              v-slot="{ Component }"
              id="content"
              class="card-body">
            <keep-alive include="Search">
              <component :is="Component"/>
            </keep-alive>
          </router-view>
          <vue-progress-bar/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapActions} from 'vuex';
import TheNotifications from '@/components/TheNotifications.vue';
import TheSidebar from '@/components/TheSidebar.vue';

export default {
  components: {
    TheNotifications,
    TheSidebar
  },

  mounted()
  {
    this.$Progress.finish();
  },

  async created()
  {
    this.$Progress.start();
    await this.getPackages();
  },

  methods: {
    ...mapActions('packages', ['getPackages'])
  }
}
</script>