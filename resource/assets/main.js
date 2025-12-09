import {httpCommon, bootstrapVueNext, vueProgressBar, router, store} from './bootstrap';
import {createApp} from 'vue';
import {progressBarInjectionKey} from './store/keys';
import App from './App.vue';

const app = createApp(App)
    .use(httpCommon)
    .use(bootstrapVueNext)
    .use(vueProgressBar)
    .use(router)
    .use(store);

app.provide(progressBarInjectionKey, app.config.globalProperties.$Progress);
app.mount('#app');