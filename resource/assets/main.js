import {
    HttpCommon,
    BootstrapVueNext,
    VueProgressBar,
    VueProgressBarExt,
    Router,
    Store
} from './bootstrap';
import {createApp} from 'vue';
import App from './App.vue';

const app = createApp(App)
    .use(HttpCommon)
    .use(BootstrapVueNext)
    .use(VueProgressBar)
    .use(VueProgressBarExt)
    .use(Router)
    .use(Store);

app.mount('#app');