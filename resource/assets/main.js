import {
    HttpCommon,
    createBootstrap,
    VueProgressBar,
    VueProgressBarExt,
    Router,
    Stores,
    PrefersScheme
} from './bootstrap';
import {createApp} from 'vue';
import App from './App.vue';

const app = createApp(App)
    .use(HttpCommon)
    .use(createBootstrap())
    .use(VueProgressBar)
    .use(VueProgressBarExt)
    .use(Router)
    .use(Stores)
    .use(PrefersScheme);

app.mount('#app');