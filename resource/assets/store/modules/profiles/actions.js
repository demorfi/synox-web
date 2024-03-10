import profiles from '@/api/profiles';

export default {
    getProfiles({dispatch, commit})
    {
        return new Promise((resolve, reject) => {
            profiles.getProfiles()
                .then(({data}) => {
                    commit('setProfiles', data);
                    resolve(data);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    createProfile: ({dispatch, commit}, {id, packages}) => {
        return new Promise((resolve, reject) => {
            const notification = dispatch('notifications/addInfo', 'Creating...', {root: true});
            profiles.createProfile(packages, id)
                .then(({data}) => {
                    commit('addProfile', data.state);
                    notification.then(({id}) => {
                        const message = 'Profile created successfully';
                        commit('notifications/setNotification', {id, message, type: 'success'}, {root: true});
                    });
                    resolve(data.state);
                })
                .catch(({message}) => {
                    notification.then(({id}) => {
                        commit('notifications/setNotification', {id, message, type: 'danger'}, {root: true});
                    });
                    reject(message);
                });
        });
    },

    updateProfile: ({dispatch, commit}, {id, packages}) => {
        return new Promise((resolve, reject) => {
            const notification = dispatch('notifications/addInfo', 'Updating...', {root: true});
            profiles.updateProfile(id, packages)
                .then(({data}) => {
                    commit('updateProfile', data.state);
                    notification.then(({id}) => {
                        const message = 'Profile updated successfully';
                        commit('notifications/setNotification', {id, message, type: 'success'}, {root: true});
                    });
                    resolve(data.state);
                })
                .catch(({message}) => {
                    notification.then(({id}) => {
                        commit('notifications/setNotification', {id, message, type: 'danger'}, {root: true});
                    });
                    reject(message);
                });
        });
    },

    removeProfile: ({dispatch, commit}, {id}) => {
        return new Promise((resolve, reject) => {
            const notification = dispatch('notifications/addInfo', 'Deleting...', {root: true});
            profiles.removeProfile(id)
                .then(() => {
                    commit('delProfile', id);
                    notification.then(({id}) => {
                        const message = 'Profile deleted successfully';
                        commit('notifications/setNotification', {id, message, type: 'success'}, {root: true});
                    });
                    resolve(id);
                })
                .catch(({message}) => {
                    notification.then(({id}) => {
                        commit('notifications/setNotification', {id, message, type: 'danger'}, {root: true});
                    });
                    reject(message);
                });
        });
    },
}