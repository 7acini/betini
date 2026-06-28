<script setup>
defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['navigate']);
</script>

<template>
    <div class="flex items-center justify-between border-t border-black/10 bg-[#faf8f2] px-5 py-4 text-sm">
        <span>Pagina {{ pagination.current_page }} de {{ pagination.last_page }}</span>
        <div class="flex gap-2">
            <button
                class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40"
                :disabled="!pagination.prev_page_url"
                type="button"
                @click="emit('navigate', pagination.prev_page_url)"
            >
                Anterior
            </button>
            <button
                class="rounded-xl border border-black/10 px-3 py-2 disabled:opacity-40"
                :disabled="!pagination.next_page_url"
                type="button"
                @click="emit('navigate', pagination.next_page_url)"
            >
                Proxima
            </button>
        </div>
    </div>
</template>
