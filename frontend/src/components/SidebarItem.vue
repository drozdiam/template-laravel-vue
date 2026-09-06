<script setup>
defineProps({
    to: { type: [String, Object], required: true },
    collapsed: { type: Boolean, default: false },
})
</script>

<template>
    <RouterLink
        :to="to"
        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-white/30"
        :class="collapsed ? 'justify-center' : ''"
        active-class="bg-white/40"
    >
        <span class="shrink-0 text-lg leading-none">
            <slot name="icon" />
        </span>
        <span v-if="!collapsed" class="truncate">
            <slot />
        </span>
    </RouterLink>
</template>
