export default {
    setJournal: (state, journal) => {
        state.journal = Object.values(journal);
    },

    clearJournal: state => state.journal = []
}