import {post} from './http-common';

export default {
    startSearch(query, filters)
    {
        return post('search/start', {query, filters})
            .then(response => {
                if (!response.data?.threads) {
                    throw new Error('No packages available to search');
                }
                return response;
            });
    }
}