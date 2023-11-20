import search from '@/api/search';

export default {
    connection({dispatch, state, commit}, {query, filters})
    {
        return new Promise((resolve, reject) => {
            commit('reset');
            search.startSearch(query, filters)
                .then(({data}) => {
                    const wsHost = data.host.replace('websocket:', 'ws:').replace('0.0.0.0', location.hostname);
                    dispatch('openSocket', {...data, wsHost})
                        .then(() => resolve(data))
                        .catch(({message}) => reject({message}));
                })
                .catch(({message}) => {
                    dispatch('notifications/addError', message, {root: true});
                    reject(message);
                });
        });
    },

    disconnection({dispatch, state})
    {
        return new Promise((resolve) => {
            if (!state.connected || !state.socket || state.socket.readyState > 1) {
                return resolve();
            }
            dispatch('closeSocket')
                .then(() => resolve());
        });
    },

    openSocket({dispatch, state, commit}, {wsHost, hash, limit})
    {
        return new Promise((resolve, reject) => {
            const socket = new WebSocket(wsHost + '/?hash=' + hash);
            socket.onopen = () => {
                commit('setSocket', socket);
                commit('setStatusConnection', true);
                resolve();
            };

            socket.onclose = (event) => {
                if (!event.wasClean && state.connected) {
                    commit('setStatusConnection', false);
                    commit('setError', event);
                    dispatch('notifications/addError', 'Connection terminated', {root: true});
                }
            };

            socket.onerror = (event) => {
                commit('setStatusConnection', false);
                commit('setError', event);
                dispatch('notifications/addError', 'There was an error with websocket', {root: true});
                reject(event);
            };

            socket.onmessage = (event) => {
                const message = JSON.parse(event.data);
                commit('setMessage', message);

                if (Object.keys(message?.payload ?? {}).length) {
                    commit('addPayload', message.payload);
                }

                if ('threads' in message) {
                    commit('setTotalThreads', message.threads);
                    commit('setLimitPayloads', message.threads * limit);
                }

                if ('error' in message) {
                    dispatch('notifications/addError', message.error, {root: true});
                }

                if ('completed' in message) {
                    commit('completedThread', message.countPayloads);
                }

                if ('finished' in message) {
                    dispatch('closeSocket');
                }
            };
        });
    },

    closeSocket({dispatch, state, commit}, {code = 1000, reason = ''} = {})
    {
        return new Promise((resolve, reject) => {
            if (state.socket.readyState > 1) {
                return reject('Socket already closed!');
            }
            state.socket?.close(code, reason);
            commit('setStatusConnection', false);
            resolve();
        });
    }
}