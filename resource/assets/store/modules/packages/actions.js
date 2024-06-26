import packages from '@/api/packages';

export default {
    getPackages({dispatch, commit})
    {
        return new Promise((resolve, reject) => {
            packages.getPackages()
                .then(({data}) => {
                    commit('setPackages', data);
                    resolve(data);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    getPackagesFilters({dispatch, commit})
    {
        return new Promise((resolve, reject) => {
            packages.getFilters()
                .then(({data}) => {
                    commit('setFilters', data);
                    resolve(data);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    changePackageActivity({dispatch, commit}, {id, active})
    {
        return new Promise((resolve, reject) => {
            packages.changeState(id, active)
                .then(({data}) => {
                    commit('updatePackageState', {id, packageState: data.state});
                    resolve(data);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    updatePackageSettings({dispatch, commit}, {id, settings})
    {
        return new Promise((resolve, reject) => {
            const notification = dispatch('notifications/addInfo', 'Updating...', {root: true});
            packages.updateSettings(id, settings)
                .then(({data}) => {
                    commit('updatePackageState', {id, packageState: data.state});
                    notification.then(({id}) => {
                        const message = 'Settings changed successfully';
                        commit('notifications/setNotification', {id, message, type: 'success'}, {root: true});
                    });
                    resolve({id, state: data.state});
                })
                .catch(({message}) => {
                    notification.then(({id}) => {
                        commit('notifications/setNotification', {id, message, type: 'danger'}, {root: true});
                    });
                    reject(message);
                });
        });
    },

    uploadPackage({dispatch, commit}, fileData)
    {
        return new Promise((resolve, reject) => {
            const notification = dispatch('notifications/addInfo', 'Uploading...', {root: true});
            packages.uploadPackage(fileData)
                .then(({data}) => packages.updatePackage(data.name))
                .then(({data}) => {
                    commit('addPackage', data.state);
                    notification.then(({id}) => {
                        const message = 'Package uploading successfully';
                        commit('notifications/setNotification', {id, message, type: 'success'}, {root: true});
                    });
                    resolve(data);
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