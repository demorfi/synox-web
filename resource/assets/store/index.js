import {createStore, createLogger} from 'vuex';
import notifications from './modules/notifications';
import settings from './modules/settings';
import journal from './modules/journal';
import packages from './modules/packages';
import search from './modules/search';
import content from './modules/content';
import profiles from './modules/profiles';

const debug = process.env.NODE_ENV !== 'production';

export default createStore({
    modules: {
        notifications,
        settings,
        journal,
        packages,
        search,
        content,
        profiles
    },
    strict : debug,
    plugins: debug ? [createLogger()] : []
})