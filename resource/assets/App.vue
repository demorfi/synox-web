<script setup lang="ts">
import {inject, onBeforeMount, onMounted} from 'vue';
import {useStore} from 'vuex';
import {progressBarInjectionKey} from './store/keys';
import TheNotifications from './components/TheNotifications.vue';
import TheSidebar from './components/TheSidebar.vue';

const store = useStore();
const progressBar = inject(progressBarInjectionKey);

onBeforeMount(async () => {
  progressBar.start();
  await store.dispatch('packages/getPackages');
  await store.dispatch('packages/getPackagesFilters');
});

onMounted(() => progressBar.finish());
</script>

<template>
  <div class="container-fluid">
    <div class="row">
      <TheSidebar/>
      <div class="col-sm p-3 min-vh-100">
        <div class="card border-0">
          <TheNotifications/>
          <RouterView id="content" class="card-body" v-slot="{ Component }">
            <KeepAlive include="Search">
              <component :is="Component"/>
            </KeepAlive>
          </RouterView>
          <vue-progress-bar/>
        </div>
      </div>
    </div>
  </div>
</template>