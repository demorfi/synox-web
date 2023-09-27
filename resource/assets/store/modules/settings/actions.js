import settings from '@/api/settings';

export default {
    getSettings: ({dispatch}) => {
        return new Promise((resolve, reject) => {
            settings.getSettings()
                .then(({data}) => {
                    for (let type in data) {
                        dispatch(`${type}/updateSettings`, data[type]);
                    }
                    resolve(data);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    updateSetting: ({dispatch, commit}, {type, name, value}) => {
        return new Promise((resolve, reject) => {
            const notification = dispatch('notifications/addInfo', 'Updating...', {root: true});
            settings.updateSetting(type, name, value)
                .then(() => {
                    dispatch(`${type}/updateSetting`, {name, value});
                    notification.then(({id}) => {
                        const message = 'Settings changed successfully';
                        commit('notifications/setNotification', {id, message, type: 'success'}, {root: true});
                    });
                    resolve({name, value});
                })
                .catch(({message}) => {
                    notification.then(({id}) => {
                        commit('notifications/setNotification', {id, message, type: 'danger'}, {root: true});
                    });
                    reject(message);
                });
        });
    }
}