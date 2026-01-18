import {ref} from 'vue';
import {defineStore} from 'pinia';
import {useNotificationsStore} from './useNotificationsStore';
import packagesApi from '@/api/packages';

export const useFiltersStore = defineStore('filters', () => {
    const {addError} = useNotificationsStore();
    const filters = ref([]);

    function set(_filters_) {
        filters.value = Object.values(_filters_);
    }

    function getById(id) {
        return filters.value.find(filter => filter.id === id);
    }

    function load() {
        return new Promise((resolve, reject) => {
            packagesApi.getFilters()
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

    return {filters, getById, load};
});