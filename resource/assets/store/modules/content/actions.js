import content from '@/api/content';

export default {
    fetchEntry({dispatch, commit}, {packageId, fetchId, params})
    {
        return new Promise((resolve, reject) => {
            content.fetch(packageId, fetchId, params)
                .then(({data}) => {
                    commit('setEntry', {packageId, fetchId, entry: data});
                    resolve(data);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    downloadEntry({dispatch, commit}, {name, typeId, baseName})
    {
        return new Promise((resolve, reject) => {
            content.download(name, baseName, typeId)
                .then(() => {
                    resolve(true);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    }
}