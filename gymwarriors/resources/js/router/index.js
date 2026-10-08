import { createRouter, createWebHistory } from 'vue-router';

import HomePage from '../pages/HomePage.vue';
import LoginPage from '../pages/LoginPage.vue';
import RegisterPage from '../pages/RegisterPage.vue';
import ProfilePage from '../pages/ProfilePage.vue';
import PlansPage from '../pages/PlansPage.vue';
import CaloriesPage from '../pages/CaloriesPage.vue';

const router = createRouter({
    history: createWebHistory(),
    scrollBehavior(to) {
        if (to.hash) {
            return { el: to.hash, behavior: 'smooth' };
        }

        return { top: 0 };
    },

    routes: [
        {
            path: '/',
            name: 'home',
            component: HomePage,
        },
        {
            path: '/login',
            name: 'login',
            component: LoginPage,
        },
        {
            path: '/register',
            name: 'register',
            component: RegisterPage,
        },
        {
            path: '/profile',
            name: 'profile',
            component: ProfilePage,
        },
        {
            path: '/plans',
            name: 'plans',
            component: PlansPage,
        },

        {
            path: '/calories',
            name: 'calories',
            component: CaloriesPage,
        },
    ],
});

export default router;