import {post} from './http-common';

export default {
    startSearch(query, filters)
    {
        return post('search/start', {query, filters})
            .then(response => {
                if (!response.data?.token) {
                    throw new Error('No token available to search');
                }
                return response;
            });
    }
}