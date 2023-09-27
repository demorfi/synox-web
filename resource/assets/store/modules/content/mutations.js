export default {
    setEntry: (state, {packageId, fetchId, entry}) => {
        state.entries[`${packageId}-${fetchId}`] = entry;
    },

    clearEntries: state => state.entries = {}
}