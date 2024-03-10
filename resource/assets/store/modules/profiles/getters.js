export default {
    getProfileById: state => (id) => {
        return state.profiles.find(profile => profile.id === id);
    }
}