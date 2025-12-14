import 'vite/modulepreload-polyfill';
import '@/sass/main.scss';

export {default as HttpCommon} from './api/http-common';
export {default as BootstrapVueNext} from 'bootstrap-vue-next';
export {default as VueProgressBar} from '@aacassandra/vue3-progressbar';
export {default as VueProgressBarExt} from './plugins/progressBarExt';
export {default as Router} from './routes';
export {default as Store} from './store';
export {default as PrefersScheme} from './plugins/prefersScheme';