import {post} from './http-common';

export default {
    startSearch(query, filters)
    {
        return post('search/start', {query, filters})
            .then(response => {
                if (!response.data?.hash) {
                    throw new Error('No hash available to search');
                }
                return response;
            });
    }
}