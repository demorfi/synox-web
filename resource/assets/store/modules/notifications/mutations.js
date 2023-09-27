export default {
    setNotification: (state, {id, type, message}) => {
        state.notifications[id] = {id, type, message};
    },

    delNotification: (state, id) => {
        if (id in state.notifications) {
            delete state.notifications[id];
        }
    },

    clearNotifications: state => state.notifications = {}
}