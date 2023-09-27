import {get, update} from './http-common';

export default {
    getSettings()
    {
        return get('settings')
            .then(response => {
                if (!Object.keys(response.data).length) {
                    throw new Error('Settings read error');
                }
                return response;
            });
    },

    updateSetting(type, name, value)
    {
        return update('settings/update', `type/${type}`, {name, value});
    }
}