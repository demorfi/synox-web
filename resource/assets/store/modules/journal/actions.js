import journal from '@/api/journal';

export default {
    getJournal({dispatch, commit})
    {
        return new Promise((resolve, reject) => {
            journal.getJournal()
                .then(({data}) => {
                    commit('setJournal', data);
                    resolve(data);
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    clearJournal({dispatch, commit})
    {
        return new Promise((resolve, reject) => {
            journal.clearJournal()
                .then(() => {
                    commit('clearJournal');
                    resolve();
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    }
}