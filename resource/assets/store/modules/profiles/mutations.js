export default {
    setProfiles: (state, profiles) => state.profiles = Object.values(profiles),
    addProfile : (state, profile) => state.profiles.push(profile),

    updateProfile: (state, profile) => {
        state.profiles.find((stateProfile, index) => {
            if (stateProfile.id === profile.id) {
                state.profiles[index] = profile;
                return true;
            }
        });
    },

    delProfile: (state, id) => {
        const index = state.profiles.findIndex(profile => profile.id === id);
        if (index !== -1) {
            state.profiles.splice(index, 1);
        }
    }
}