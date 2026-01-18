import {ref} from 'vue';
import {defineStore} from 'pinia';
import {useNotificationsStore} from './useNotificationsStore';
import searchApi from '@/api/search';

export const useSearchStore = defineStore('search', () => {
    const {addError} = useNotificationsStore();
    const connected = ref(false);
    const payloads = ref([]);
    const limitPayloads = ref(0);
    const progressToLimit = ref(0);

    let connection = null;
    let totalThreads = 0;
    let completedThreads = 0;

    function addPayload(payload) {
        payloads.value.push(payload);
        progressToLimit.value += 1;
    }

    function setLimitPayloads(limit) {
        limitPayloads.value = limit;
    }

    function setTotalThreads(threads) {
        totalThreads = threads;
        completedThreads = 0;
    }

    function setConnection(socket) {
        connection = socket;
    }

    function setStatusConnection(status) {
        connected.value = status;
    }

    function completedThread() {
        completedThreads += 1;
        progressToLimit.value = (limitPayloads.value / totalThreads) * completedThreads;
    }

    function clearPayloads() {
        payloads.value = []
    }

    function $reset() {
        limitPayloads.value = 0;
        progressToLimit.value = 0;
        totalThreads = 0;
        completedThreads = 0;
    }

    function connect(query, profile, filters) {
        return new Promise((resolve, reject) => {
            $reset();
            searchApi.startSearch(query, profile, filters)
                .then(({data}) => {
                    const wsHost = data.host
                        .replace('websocket:', 'ws:')
                        .replace('0.0.0.0', location.hostname);

                    openSocket(wsHost, data.token, data.limit)
                        .then(() => resolve(data))
                        .catch(({message}) => reject({message}));
                })
                .catch(({message}) => {
                    addError(message);
                    reject(message);
                });
        });
    }

    function disconnect() {
        return new Promise(resolve => {
            if (!connected.value || !connection || connection?.readyState > 1) {
                return resolve();
            }
            closeSocket().then(() => resolve());
        });
    }

    function openSocket(wsHost, token, limit) {
        return new Promise((resolve, reject) => {
            const socket = new WebSocket(wsHost + '/?token=' + token);
            socket.onopen = () => {
                setConnection(socket);
                setStatusConnection(true);
                resolve();
            };

            socket.onclose = (event) => {
                if (!event.wasClean && connected.value) {
                    setStatusConnection(false);
                    addError('Connection terminated');
                }
            };

            socket.onerror = (event) => {
                setStatusConnection(false);
                addError('There was an error with websocket');
                reject(event);
            };

            socket.onmessage = (event) => {
                const message = JSON.parse(event.data);
                if (Object.keys(message?.payload ?? {}).length) {
                    addPayload(message.payload);
                }

                if ('threads' in message) {
                    setTotalThreads(message.threads);
                    setLimitPayloads(message.threads * limit);
                }

                if ('error' in message) {
                    addError(message.error);
                }

                if ('completed' in message) {
                    completedThread();
                }

                if ('finished' in message) {
                    closeSocket();
                }
            };
        });
    }

    function closeSocket({code = 1000, reason = ''} = {}) {
        return new Promise((resolve, reject) => {
            if (connection?.readyState > 1) {
                return reject('Socket already closed!');
            }
            connection?.close(code, reason);
            setStatusConnection(false);
            resolve();
        });
    }

    return {
        connected,
        payloads,
        limitPayloads,
        progressToLimit,
        clearPayloads,
        connect,
        disconnect,
        $reset
    };
});