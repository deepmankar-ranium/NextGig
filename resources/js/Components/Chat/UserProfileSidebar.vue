<template>
    <div v-if="user" class="w-[300px] bg-white border-l border-slate-200 p-6 overflow-y-auto">
        <div class="text-center mb-6">
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-white font-semibold text-3xl mx-auto mb-3 shadow-lg bg-gradient-to-br from-violet-600 to-violet-500">
                {{ getUserInitials(user) }}
            </div>
            <h2 class="text-lg font-semibold text-slate-800 mb-1">
                {{ getUserDisplayName(user) }}
            </h2>
            <p class="text-sm text-emerald-500 flex items-center justify-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                UX Designer
            </p>
        </div>

        <div class="flex gap-2 mb-6">
            <button class="flex-1 p-2 border border-slate-200 rounded-lg bg-white text-slate-500 text-xs flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { getUserDisplayName, getUserInitials } from '@/utils/helpers';

defineProps({
    user: Object,
});
</script>
