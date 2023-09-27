export default {
    updateSettings: ({commit, dispatch}, settings) => {
        for (let name in settings) {
            dispatch('updateSetting', {name, value: settings[name]});
        }
    },

    updateSetting: ({commit}, {name, value}) => {
        commit('setSetting', {name, value});
    }
}