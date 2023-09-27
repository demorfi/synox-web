import {createStore, createLogger} from 'vuex';
import notifications from './modules/notifications';
import settings from './modules/settings';
import journal from './modules/journal';
import packages from './modules/packages';
import search from './modules/search';
import content from './modules/content';

const debug = process.env.NODE_ENV !== 'production';

export default createStore({
    modules: {
        notifications,
        settings,
        journal,
        packages,
        search,
        content
    },
    strict : debug,
    plugins: debug ? [createLogger()] : []
})