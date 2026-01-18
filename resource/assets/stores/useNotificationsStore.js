import {ref} from 'vue';
import {defineStore} from 'pinia';

export const useNotificationsStore = defineStore('notifications', () => {
    const notifications = ref({});

    function $reset() {
        notifications.value = {};
    }

    function setNotification(id, type, message) {
        notifications.value[id] = {id, type, message};
        return notifications.value[id];
    }

    function delNotification(id) {
        if (id in notifications.value) {
            delete notifications.value[id];
        }
    }

    function addNotification(type = 'danger', message) {
        const id = `notification_${Date.now().toString(36)}${Math.random().toString(36).substring(2, 11)}`;
        return setNotification(id, type, message);
    }

    function addError(message) {
        return addNotification('danger', message);
    }

    function addSuccess(message) {
        return addNotification('success', message);
    }

    function addInfo(message) {
        return addNotification('info', message);
    }

    return {
        notifications,
        $reset,
        setNotification,
        delNotification,
        addError,
        addSuccess,
        addInfo
    };
});