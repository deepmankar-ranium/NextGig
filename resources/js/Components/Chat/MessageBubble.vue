<template>
    <div class="flex gap-3 mb-5" :class="isMyMessage ? 'flex-row-reverse' : ''">
        <div v-if="!isMyMessage" class="flex gap-3 max-w-[70%]">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 bg-gradient-to-br from-violet-600 to-violet-500">
                {{ getUserInitials(user) }}
            </div>
            <div class="bg-white rounded-2xl rounded-tl-md p-3.5 shadow-sm border border-slate-200"
                 @mouseenter="$emit('mouseenter', message, $event)"
                 @mouseleave="$emit('mouseleave')">
                <p class="text-slate-800 text-sm leading-relaxed m-0">{{ getMessageText(message) }}</p>
                <p class="text-[11px] text-slate-500 mt-1">{{ formatTime(message.created_at) }}</p>
                <p> {{ message.id }}</p>
            </div>
        </div>

        <div v-else class="flex gap-3 max-w-[70%]">
            <div class="bg-violet-600 rounded-2xl rounded-tr-md p-3.5 shadow-sm"
                 @mouseenter="$emit('mouseenter', message, $event)"
                 @mouseleave="$emit('mouseleave')">
                <p class="text-white text-sm leading-relaxed m-0">{{ getMessageText(message) }}</p>
                <div class="flex items-center justify-end gap-1 mt-1">
                    <p class="text-[11px] text-white/80 m-0">{{ formatTime(message.created_at) }}</p>
                    <svg class="w-[14px] h-[14px] text-white/80" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                    </svg>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 bg-gradient-to-br from-emerald-500 to-emerald-600">
                {{ getUserInitials(currentUser) }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { formatTime, getUserInitials, getMessageText } from '@/utils/helpers';

const props = defineProps({
    message: Object,
    user: Object,
    currentUser: Object,
});

defineEmits(['mouseenter', 'mouseleave']);

const isMyMessage = computed(() => props.message.sender_id === props.currentUser.id);
</script>
