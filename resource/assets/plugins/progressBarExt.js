import {progressBarInjectionKey} from '@/store/keys.js';

export default {
    install(app) {
        app.provide(progressBarInjectionKey, app.config.globalProperties.$Progress);
    }
}