<script setup>
import { useI18n } from 'vue-i18n'
import SidebarItem from './SidebarItem.vue'

defineProps({
    collapsed: { type: Boolean, default: false },
})

const { t } = useI18n()
</script>

<template>
    <aside
        class="flex h-full flex-col overflow-hidden bg-white/40 backdrop-blur-sm transition-all duration-300"
        :class="collapsed ? 'w-16' : 'w-64'"
    >
        <div class="flex h-16 shrink-0 items-center border-b border-white/30 px-4">
            <span v-if="!collapsed" class="text-lg font-bold text-gray-800">App</span>
            <span v-else class="mx-auto text-lg font-bold text-gray-800">A</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-3">
            <SidebarItem :to="{ name: 'dashboard' }" :collapsed="collapsed">
                <template #icon>🏠</template>
                {{ t('nav.dashboard') }}
            </SidebarItem>
        </nav>
    </aside>
</template>
