export default {
    getEntry: (state) => ({packageId, fetchId}) => {
        return state.entries[`${packageId}-${fetchId}`];
    }
}