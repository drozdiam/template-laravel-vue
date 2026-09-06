<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import Sidebar from '@/components/Sidebar.vue'

const { t } = useI18n()
const auth = useAuthStore()
const router = useRouter()

const collapsed = ref(false)
const drawerOpen = ref(false)

async function logout() {
    await auth.logout()
    router.push({ name: 'login' })
}
</script>

<template>
    <div
        class="flex h-screen overflow-hidden"
        style="background-image: url('/bg.png'); background-size: cover; background-position: center;"
    >
        <!-- Desktop sidebar -->
        <div class="hidden md:flex md:shrink-0">
            <Sidebar :collapsed="collapsed" />
        </div>

        <!-- Mobile drawer overlay -->
        <Transition name="fade">
            <div
                v-if="drawerOpen"
                class="fixed inset-0 z-20 bg-black/30 md:hidden"
                @click="drawerOpen = false"
            />
        </Transition>

        <!-- Mobile drawer -->
        <Transition name="slide">
            <div v-if="drawerOpen" class="fixed inset-y-0 left-0 z-30 md:hidden">
                <Sidebar :collapsed="false" />
            </div>
        </Transition>

        <!-- Main area -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="flex h-16 shrink-0 items-center gap-4 border-b border-white/30 bg-white/40 px-4 backdrop-blur-sm">
                <!-- Mobile burger -->
                <button
                    class="rounded-lg p-1.5 hover:bg-white/30 md:hidden"
                    @click="drawerOpen = true"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Desktop collapse toggle -->
                <button
                    class="hidden rounded-lg p-1.5 hover:bg-white/30 md:block"
                    @click="collapsed = !collapsed"
                >
                    <svg class="h-5 w-5 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>

                <div class="ml-auto flex items-center gap-4">
                    <span class="text-sm text-gray-700">{{ auth.user?.name }}</span>
                    <button
                        class="rounded-lg px-3 py-1.5 text-sm text-gray-600 hover:bg-white/30"
                        @click="logout"
                    >
                        {{ t('auth.logout') }}
                    </button>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-enter-active, .slide-leave-active { transition: transform 0.25s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>
