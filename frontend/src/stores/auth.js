import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const checked = ref(false)

    async function fetchUser() {
        try {
            const { data } = await api.get('/auth/me')
            user.value = data
        } catch {
            user.value = null
            localStorage.removeItem('token')
        } finally {
            checked.value = true
        }
    }

    async function login(credentials) {
        const { data } = await api.post('/auth/login', credentials)
        localStorage.setItem('token', data.access_token)
        await fetchUser()
    }

    async function register(payload) {
        const { data } = await api.post('/auth/register', payload)
        localStorage.setItem('token', data.access_token)
        await fetchUser()
    }

    async function logout() {
        try {
            await api.post('/auth/logout')
        } finally {
            localStorage.removeItem('token')
            user.value = null
            checked.value = false
        }
    }

    return { user, checked, fetchUser, login, register, logout }
})
