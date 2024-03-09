import axios from 'axios';

const defCatch = (error) => {
    throw new Error(error.response?.data?.error ?? error.message);
};

export const http = axios.create({
    baseURL: '/api/'
});

export function get(resource)
{
    return http.get(`${resource}`).catch(defCatch);
}

export function download(resource)
{
    return http.get(`${resource}`, {responseType: 'blob'}).catch(defCatch);
}

export function post(resource, params)
{
    return http.post(`${resource}`, params).catch(defCatch);
}

export function update(resource, slug, params)
{
    return http.put(`${resource}/${slug}`, params).catch(defCatch);
}

export function remove(resource)
{
    return http.delete(`${resource}`).catch(defCatch);
}

export default {
    install: (app) => {
        app.provide('http', http);

        http.interceptors.request.use((config) => {
            app.config.globalProperties.$Progress.start();
            return config;
        });

        http.interceptors.response.use((response) => {
            app.config.globalProperties.$Progress.finish();
            return response;
        }, (error) => {
            app.config.globalProperties.$Progress.finish();
            defCatch(error);
        });
    }
}