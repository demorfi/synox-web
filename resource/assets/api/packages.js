import {get, update} from './http-common';

export default {
    getPackages()
    {
        return get('packages')
            .then(response => {
                if (!Object.keys(response.data).length) {
                    throw new Error('Packages read error');
                }
                return response;
            });
    },

    getFilters()
    {
        return get('packages/filters')
            .then(response => {
                if (!Object.keys(response.data).length) {
                    throw new Error('Packages filters read error');
                }
                return response;
            });
    },

    changeState(id, enabled)
    {
        return update('packages', `changeState/id/${id}`, {enabled})
            .then(response => {
                if (!('enabled' in response.data)) {
                    throw new Error('Failed to change package state');
                }
                return response;
            });
    },

    updateSettings(id, settings)
    {
        return update('packages', `updateSettings/id/${id}`, {settings});
    }
}