import {progressBarInjectionKey} from '@/stores/keys.js';

export default {
    install(app) {
        app.provide(progressBarInjectionKey, app.config.globalProperties.$Progress);
    }
}