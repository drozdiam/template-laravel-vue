import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
    {
        path: '/',
        component: () => import('@/layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/pages/Dashboard.vue'),
            },
        ],
    },
    {
        path: '/',
        component: () => import('@/layouts/GuestLayout.vue'),
        meta: { requiresGuest: true },
        children: [
            {
                path: 'login',
                name: 'login',
                component: () => import('@/pages/Login.vue'),
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('@/pages/Register.vue'),
            },
        ],
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to) => {
    const auth = useAuthStore()

    if (!auth.checked) {
        await auth.fetchUser()
    }

    if (to.meta.requiresAuth && !auth.user) {
        return { name: 'login' }
    }

    if (to.meta.requiresGuest && auth.user) {
        return { name: 'dashboard' }
    }
})

export default router
