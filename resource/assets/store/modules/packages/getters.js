export default {
    getPackageById: state => (id) => {
        return state.packages.find(pkg => pkg.id === id);
    },

    getPackagesByType: state => (type) => {
        return state.packages.filter(pkg => pkg.type === type);
    },

    getPackagesEnabled: state => {
        return state.packages.filter(pkg => pkg.enabled && pkg.available);
    },

    getPackagesEnabledByType: state => (type) => {
        return state.packages.filter(pkg => pkg.type === type && pkg.enabled && pkg.available);
    },

    getPackageSettings: (state, getters) => (id) => {
        return getters.getPackageById(id).settings ?? [];
    }
}