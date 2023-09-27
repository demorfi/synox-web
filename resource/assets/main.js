import {httpCommon, bootstrapVueNext, vueProgressBar, router, store} from './bootstrap';
import {createApp} from 'vue';
import App from './App.vue';

const app = createApp(App)
    .use(httpCommon)
    .use(bootstrapVueNext)
    .use(vueProgressBar)
    .use(router)
    .use(store);

app.mount('#app');