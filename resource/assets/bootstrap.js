import 'vite/modulepreload-polyfill';
import '@/sass/main.scss';

export {default as HttpCommon} from './api/http-common';
export {createBootstrap} from 'bootstrap-vue-next/plugins/createBootstrap';
export {default as VueProgressBar} from '@aacassandra/vue3-progressbar';
export {default as VueProgressBarExt} from './plugins/progressBarExt';
export {default as Router} from './routes';
export {default as Stores} from './stores';
export {default as PrefersScheme} from './plugins/prefersScheme';