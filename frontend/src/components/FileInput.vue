<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
    accept: { type: String, default: 'image/*' },
    error: { type: String, default: null },
})

const emit = defineEmits(['change'])

const { t } = useI18n()
const inputRef = ref(null)
const fileName = ref(null)

const label = computed(() => fileName.value ?? t('common.no_file'))

function onFileChange(e) {
    const file = e.target.files[0] || null
    fileName.value = file ? file.name : null
    emit('change', file)
}
</script>

<template>
    <div>
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="rounded-lg border border-gray-300 bg-white/80 px-3 py-2 text-sm text-gray-700 hover:bg-white focus:outline-none"
                @click="inputRef.click()"
            >
                {{ t('common.choose_file') }}
            </button>
            <span class="truncate text-sm text-gray-500">{{ label }}</span>
            <input
                ref="inputRef"
                type="file"
                :accept="accept"
                class="hidden"
                @change="onFileChange"
            />
        </div>
        <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
    </div>
</template>