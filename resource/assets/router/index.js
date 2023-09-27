import {createRouter, createWebHistory} from 'vue-router';

const routes = [
    {
        path     : '/',
        name     : 'Search',
        meta     : {icon: 'search', badge: {id: 'search', variant: 'primary'}},
        component: () => import('@/views/search.vue')
    },
    {
        path     : '/packages',
        name     : 'Packages',
        meta     : {icon: 'box-seam', badge: {id: 'packages', variant: 'light'}},
        component: () => import('@/views/packages.vue')
    },
    {
        path     : '/settings',
        name     : 'Settings',
        meta     : {icon: 'gear'},
        component: () => import('@/views/settings.vue')
    },
    {
        path     : '/journal',
        name     : 'Journal',
        meta     : {icon: 'journal'},
        component: () => import('@/views/journal.vue')
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default {
    install(app)
    {
        router.beforeEach((to, from, next) => {
            app.config.globalProperties.$Progress.start();
            next();
        });

        router.afterEach(() => {
            app.config.globalProperties.$Progress.finish();
        });

        router.install(app);
    }
}