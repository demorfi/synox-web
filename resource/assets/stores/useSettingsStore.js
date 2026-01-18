import {reactive} from 'vue';
import {defineStore} from 'pinia';
import {useNotificationsStore} from './useNotificationsStore';
import settingsApi from '@/api/settings';

export const useSettingsStore = defineStore('settings', () => {
    const {addError, addInfo, setNotification} = useNotificationsStore();
    const settings = reactive({});

    function load() {
        return new Promise((resolve, reject) => {
            settingsApi.getSettings()
                .then(({data}) => {
                    for (let type in data) {
                        settings[type] = {};
                        for (let name in data[type]) {
                            settings[type][name] = data[type][name];
                        }
                    }
                    resolve(data);
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    function update(type, name, value) {
        return new Promise((resolve, reject) => {
            const notification = addInfo('Updating...');
            settingsApi.updateSetting(type, name, value)
                .then(() => {
                    settings[type][name] = value;
                    setNotification(notification.id, 'success', 'Settings changed successfully');
                    resolve({name, value});
                })
                .catch(({message}) => {
                    setNotification(notification.id, 'danger', message);
                    reject(message);
                });
        });
    }

    return {settings, load, update};
});