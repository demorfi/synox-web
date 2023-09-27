import state from './state';
import getters from './getters';
import actions from './actions';
import mutations from './mutations';
import app from './app';

export default {
    namespaced: true,
    modules   : {
        app
    },
    state,
    getters,
    actions,
    mutations
}