import {get, remove} from './http-common';

export default {
    getJournal()
    {
        return get('journal');
    },

    clearJournal()
    {
        return remove('journal');
    }
}