import {ref} from 'vue';
import {defineStore} from 'pinia';
import {useNotificationsStore} from './useNotificationsStore';
import contentApi from '@/api/content';

export const useEntriesStore = defineStore('entries', () => {
    const {addError} = useNotificationsStore();
    const entries = ref({});

    function set(packageId, fetchId, entry) {
        entries.value[`${packageId}-${fetchId}`] = entry;
    }

    function $reset() {
        entries.value = {};
    }

    function get(packageId, fetchId) {
        return entries.value[`${packageId}-${fetchId}`];
    }

    function fetch(packageId, fetchId, params) {
        return new Promise((resolve, reject) => {
            contentApi.fetch(packageId, fetchId, params)
                .then(({data}) => {
                    set(packageId, fetchId, data);
                    resolve(data);
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    function download(name, typeId, baseName) {
        return new Promise((resolve, reject) => {
            contentApi.download(name, baseName, typeId)
                .then(() => {
                    resolve(true);
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    return {entries, $reset, get, fetch, download};
});