<script setup>
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const { t } = useI18n()
const auth = useAuthStore()
const router = useRouter()

const form = reactive({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function submit() {
    error.value = ''
    loading.value = true
    try {
        await auth.login(form)
        router.push({ name: 'dashboard' })
    } catch (e) {
        error.value = e.response?.data?.message ?? t('auth.error_login')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="rounded-xl bg-white p-8 shadow">
        <h1 class="mb-6 text-2xl font-bold text-gray-900">{{ t('auth.login') }}</h1>

        <p v-if="error" class="mb-4 rounded bg-red-50 p-3 text-sm text-red-600">
            {{ error }}
        </p>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ t('auth.email') }}</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ t('auth.password') }}</label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
            </div>
            <button
                type="submit"
                :disabled="loading"
                class="w-full rounded-lg bg-blue-600 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            >
                {{ loading ? t('auth.signing_in') : t('auth.login') }}
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            {{ t('auth.no_account') }}
            <RouterLink :to="{ name: 'register' }" class="text-blue-600 hover:underline">
                {{ t('auth.register') }}
            </RouterLink>
        </p>
    </div>
</template>
