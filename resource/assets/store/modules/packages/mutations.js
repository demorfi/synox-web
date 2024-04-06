export default {
    setPackages: (state, packages) => state.packages = Object.values(packages),
    setFilters : (state, filters) => state.filters = Object.values(filters),

    addPackage : (state, pkgState) => {
        const index = state.packages.findIndex(pkg => pkg.id === pkgState.id);
        if (index !== -1) {
            state.packages[index] = pkgState;
        } else {
            state.packages.push(pkgState);
        }
    },

    setPackageActivity: (state, {id, active}) => {
        state.packages.find((pkg, index) => {
            if (pkg.id === id) {
                state.packages[index].enabled = active;
                return true;
            }
        });
    },

    updatePackageState: (state, {id, packageState}) => {
        state.packages.find((pkg, index) => {
            if (pkg.id === id) {
                state.packages[index] = packageState;
                return true;
            }
        });
    },

    setPackageSettings: (state, {id, settings}) => {
        state.packages.find((pkg, index) => {
            if (pkg.id === id) {
                for (let setting in settings) {
                    const pkgSettings = state.packages[index].settings;
                    if (setting in pkgSettings) {
                        if (typeof pkgSettings[setting] === 'object' && 'value' in pkgSettings[setting]) {
                            pkgSettings[setting].value = settings[setting];
                        } else {
                            pkgSettings[setting] = settings[setting];
                        }
                    }
                }
                return true;
            }
        });
    },
}