export default {
    getPackageById: state => (id) => {
        return state.packages.find(pkg => pkg.id === id);
    },

    getPackagesEnabled: state => {
        return state.packages.filter(pkg => pkg.enabled);
    },

    getPackageSettings: (state, getters) => (id) => {
        return getters.getPackageById(id).settings ?? [];
    }
}