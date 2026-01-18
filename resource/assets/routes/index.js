import {createRouter, createWebHistory} from 'vue-router';
import {usePackagesStore} from '@/stores/usePackagesStore';
import {useSearchStore} from '@/stores/useSearchStore';

const routes = [
    {
        path: '/',
        name: 'Search',
        meta: {
            icon: 'search', badge: {
                id: 'search', variant: 'primary', payload: () => {
                    const store = useSearchStore();
                    return store.payloads.length || false;
                }
            }
        },
        component: () => import('@/pages/search.vue')
    },
    {
        path: '/profiles',
        name: 'Profiles',
        meta: {icon: 'collection'},
        component: () => import('@/pages/profiles.vue')
    },
    {
        path: '/packages',
        name: 'Packages',
        meta: {
            icon: 'box-seam', badge: {
                id: 'packages', variant: 'light', payload: () => {
                    const store = usePackagesStore();
                    return store.getEnabled().length || false;
                }
            }
        },
        component: () => import('@/pages/packages.vue')
    },
    {
        path: '/settings',
        name: 'Settings',
        meta: {icon: 'gear'},
        component: () => import('@/pages/settings.vue')
    },
    {
        path: '/journal',
        name: 'Journal',
        meta: {icon: 'journal'},
        component: () => import('@/pages/journal.vue')
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default {
    install(app) {
        router.install(app);
    }
}