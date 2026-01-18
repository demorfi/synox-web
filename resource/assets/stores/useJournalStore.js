import {ref} from 'vue';
import {defineStore} from 'pinia';
import {useNotificationsStore} from './useNotificationsStore';
import journalApi from '@/api/journal';

export const useJournalStore = defineStore('journal', () => {
    const {addError} = useNotificationsStore();
    const records = ref([]);

    function $reset() {
        records.value = [];
    }

    function set(journal) {
        records.value = Object.values(journal);
    }

    function load() {
        return new Promise((resolve, reject) => {
            journalApi.getJournal()
                .then(({data}) => {
                    set(data);
                    resolve(data);
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    function clear() {
        return new Promise((resolve, reject) => {
            journalApi.clearJournal()
                .then(() => {
                    $reset();
                    resolve();
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    return {records, $reset, load, clear};
});