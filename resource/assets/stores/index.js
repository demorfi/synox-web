import {createPinia} from 'pinia';

const stores = createPinia();

export default {
    install(app) {
        stores.install(app);
    }
}