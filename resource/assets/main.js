import {
    HttpCommon,
    BootstrapVueNext,
    VueProgressBar,
    VueProgressBarExt,
    Router,
    Store,
    PrefersScheme
} from './bootstrap';
import {createApp} from 'vue';
import App from './App.vue';

const app = createApp(App)
    .use(HttpCommon)
    .use(BootstrapVueNext)
    .use(VueProgressBar)
    .use(VueProgressBarExt)
    .use(Router)
    .use(Store)
    .use(PrefersScheme);

app.mount('#app');