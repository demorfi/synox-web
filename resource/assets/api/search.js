import {post} from './http-common';

export default {
    startSearch(query, profile, filters)
    {
        return post('search/start', {query, profile, filters})
            .then(response => {
                if (!response.data?.token) {
                    throw new Error('No token available to search');
                }
                return response;
            });
    }
}