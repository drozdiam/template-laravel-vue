<script setup>
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const auth = useAuthStore()
</script>

<template>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ t('dashboard.welcome', { name: auth.user?.name }) }}
        </h1>
        <p class="mt-2 text-gray-600">{{ t('dashboard.subtitle') }}</p>
    </div>
</template>
