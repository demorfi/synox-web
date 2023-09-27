export default {
    setPackages: (state, packages) => state.packages = Object.values(packages),
    setFilters : (state, filters) => state.filters = Object.values(filters),

    setPackageActivity: (state, {id, active}) => {
        state.packages.find((pkg, index) => {
            if (pkg.id === id) {
                state.packages[index].enabled = active;
                return true;
            }
        });
    },

    setPackageSettings: (state, {id, settings}) => {
        state.packages.find((pkg, index) => {
            if (pkg.id === id) {
                state.packages[index].settings = {...state.packages[index].settings, ...settings};
                return true;
            }
        });
    },
}