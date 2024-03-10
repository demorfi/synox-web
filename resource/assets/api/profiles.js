import {get, post, update, remove} from './http-common';

export default {
    getProfiles()
    {
        return get('profiles');
    },

    createProfile(packages, id)
    {
        return post('profiles/create', {packages, id});
    },

    updateProfile(id, packages)
    {
        return update('profiles/profile', `id/${id}`, {packages});
    },

    removeProfile(id)
    {
        return remove(`profiles/profile/id/${id}`);
    }
}