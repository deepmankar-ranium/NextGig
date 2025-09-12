<template>
    <div class="h-[60px] bg-white border-b border-slate-200 px-6 flex items-center justify-between shadow-sm">
        <div v-if="user" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-base bg-gradient-to-br from-violet-600 to-violet-500">
                {{ getUserInitials(user) }}
            </div>
            <div>
                <h1 class="text-base font-semibold text-slate-800 m-0">
                    {{ getUserDisplayName(user) }}
                </h1>
                <p class="text-xs text-emerald-500 flex items-center gap-1 m-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Online
                </p>
            </div>
        </div>

        <div v-if="user" class="flex items-center gap-2">
            <button v-if="hasMessages" @click="$emit('delete-messages')" class="p-2 rounded-full text-slate-500 hover:bg-red-100 hover:text-red-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
            <button class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center transition hover:bg-slate-200">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            </button>
            <button class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center transition hover:bg-slate-200">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </button>
            <button class="w-9 h-9 rounded-full bg-violet-600 text-white flex items-center justify-center transition hover:bg-violet-700">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { getUserDisplayName, getUserInitials } from '@/utils/helpers';

defineProps({
    user: Object,
    hasMessages: Boolean,
});

defineEmits(['delete-messages']);
</script>
