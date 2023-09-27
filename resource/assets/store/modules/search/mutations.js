export default {
    setMessage         : (state, message) => state.lastMessage = message,
    setError           : (state, event) => state.lastError = event,
    addPayload         : (state, payload) => {
        state.payloads.push(payload);
        state.progressToLimit += 1;
    },
    setLimitPayloads   : (state, limit) => state.limitPayloads = limit,
    setTotalThreads    : (state, threads) => {
        state.totalThreads = threads;
        state.completedThreads = 0;
        state.foundInThreads = [];
    },
    setSocket          : (state, socket) => state.socket = socket,
    setStatusConnection: (state, status) => state.connected = status,
    completedThread    : (state, countPayloads) => {
        state.foundInThreads[state.completedThreads] = countPayloads;
        state.completedThreads += 1;
        state.progressToLimit = (state.limitPayloads / state.totalThreads) * state.completedThreads;
    },
    clearPayloads      : state => state.payloads = [],
    reset              : state => {
        state.lastError = null;
        state.lastMessage = {};
        state.limitPayloads = 0;
        state.totalThreads = 0;
        state.completedThreads = 0;
        state.progressToLimit = 0;
        state.foundInThreads = [];
    }
}