import {ref} from 'vue';
import {defineStore} from 'pinia';
import {useNotificationsStore} from './useNotificationsStore';
import packagesApi from '@/api/packages';

export const usePackagesStore = defineStore('packages', () => {
    const {addError, addInfo, setNotification} = useNotificationsStore();
    const packages = ref([]);

    function set(_packages_) {
        packages.value = Object.values(_packages_);
    }

    function getById(id) {
        return packages.value.find(pkg => pkg.id === id);
    }

    function getByType(type) {
        return packages.value.filter(pkg => pkg.type === type);
    }

    function getEnabled() {
        return packages.value.filter(pkg => pkg.enabled && pkg.available);
    }

    function getEnabledByType(type) {
        return packages.value.filter(pkg => pkg.type === type && pkg.enabled && pkg.available);
    }

    function getSettings(id) {
        return getById(id).settings ?? [];
    }

    function addPackage(pkgState) {
        const index = packages.value.findIndex(pkg => pkg.id === pkgState.id);
        if (index !== -1) {
            packages.value[index] = pkgState;
        } else {
            packages.value.push(pkgState);
        }
    }

    function updateState(id, pkgState) {
        packages.value.find((pkg, index) => {
            if (pkg.id === id) {
                packages.value[index] = pkgState;
                return true;
            }
        });
    }

    function load() {
        return new Promise((resolve, reject) => {
            packagesApi.getPackages()
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

    function upload(fileData) {
        return new Promise((resolve, reject) => {
            const notification = addInfo('Uploading...');
            packagesApi.uploadPackage(fileData)
                .then(({data}) => packagesApi.updatePackage(data.name))
                .then(({data}) => {
                    addPackage(data.state);
                    setNotification(notification.id, 'success', 'Package uploading successfully');
                    resolve(data);
                })
                .catch(({message}) => {
                    setNotification(notification.id, 'danger', message);
                    reject(message);
                });
        });
    }

    function changeActivity(id, active) {
        return new Promise((resolve, reject) => {
            packagesApi.changeState(id, active)
                .then(({data}) => {
                    updateState(id, data.state);
                    resolve(data);
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    function updateSettings(id, settings) {
        return new Promise((resolve, reject) => {
            const notification = addInfo('Updating...');
            packagesApi.updateSettings(id, settings)
                .then(({data}) => {
                    updateState(id, data.state);
                    setNotification(notification.id, 'success', 'Settings changed successfully');
                    resolve({id, state: data.state});
                })
                .catch(({message}) => {
                    setNotification(notification.id, 'danger', message);
                    reject(message);
                });
        });
    }

    return {
        packages,
        getById,
        getByType,
        getEnabled,
        getEnabledByType,
        getSettings,
        load,
        updateState,
        changeActivity,
        updateSettings,
        upload
    };
});