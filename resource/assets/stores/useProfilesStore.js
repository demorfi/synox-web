import {ref} from 'vue';
import {defineStore} from 'pinia';
import {useNotificationsStore} from './useNotificationsStore';
import profilesApi from '@/api/profiles';

export const useProfilesStore = defineStore('profiles', () => {
    const {addError, addInfo, setNotification} = useNotificationsStore();
    const profiles = ref([]);

    function getById(id) {
        return profiles.value.find(profile => profile.id === id);
    }

    function load() {
        return new Promise((resolve, reject) => {
            profilesApi.getProfiles()
                .then(({data}) => {
                    profiles.value = Object.values(data);
                    resolve(data);
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    function create(id, packages) {
        return new Promise((resolve, reject) => {
            const notification = addInfo('Creating...');
            profilesApi.createProfile(packages, id)
                .then(({data}) => {
                    profiles.value.push(data.state);
                    setNotification(notification.id, 'success', 'Profile created successfully');
                    resolve(data.state);
                })
                .catch(({message}) => {
                    setNotification(notification.id, 'danger', message);
                    reject(message);
                });
        });
    }

    function update(id, packages) {
        return new Promise((resolve, reject) => {
            const notification = addInfo('Updating...');
            profilesApi.updateProfile(id, packages)
                .then(({data}) => {
                    profiles.value.find((stateProfile, index) => {
                        if (stateProfile.id === data.state.id) {
                            profiles.value[index] = data.state;
                            return true;
                        }
                    });
                    setNotification(notification.id, 'success', 'Profile updated successfully');
                    resolve(data.state);
                })
                .catch(({message}) => {
                    setNotification(notification.id, 'danger', message);
                    reject(message);
                });
        });
    }

    function remove(id) {
        return new Promise((resolve, reject) => {
            const notification = addInfo('Deleting...');
            profilesApi.removeProfile(id)
                .then(() => {
                    const index = profiles.value.findIndex(profile => profile.id === id);
                    if (index !== -1) {
                        profiles.value.splice(index, 1);
                    }
                    setNotification(notification.id, 'success', 'Profile deleted successfully');
                    resolve(id);
                })
                .catch(({message}) => {
                    setNotification(notification.id, 'danger', message);
                    reject(message);
                });
        });
    }

    return {profiles, getById, load, create, update, remove};
});