import _ from 'lodash';

export default {
    addNotification: ({commit, getters}, {type = 'danger', message}) => {
        let id = _.uniqueId('notification_'),
            notification = {id, type, message};

        commit('setNotification', notification);
        return notification;
    },

    addError: ({dispatch}, message) => {
        return dispatch('addNotification', {type: 'danger', message});
    },

    addSuccess: ({dispatch}, message) => {
        return dispatch('addNotification', {type: 'success', message});
    },

    addInfo: ({dispatch}, message) => {
        return dispatch('addNotification', {type: 'info', message});
    }
}