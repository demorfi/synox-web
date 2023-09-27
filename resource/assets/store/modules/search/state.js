export default {
    socket          : null,
    connected       : false,
    lastError       : null,
    lastMessage     : {},
    payloads        : [],
    limitPayloads   : 0,
    totalThreads    : 0,
    completedThreads: 0,
    progressToLimit : 0,
    foundInThreads  : []
}