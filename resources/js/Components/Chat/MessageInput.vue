<template>
    <div class="p-5 bg-white border-t border-slate-200">
        <div class="flex items-end gap-3">
            <div class="flex items-center gap-3 bg-slate-100 rounded-full px-5 py-3 border-2 border-transparent transition focus-within:border-violet-500 w-full">
                <button class="w-6 h-6 text-slate-500 flex items-center justify-center hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </button>

                <input type="text" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" @keyup.enter="$emit('send')" placeholder="Type your message here..." :disabled="disabled" class="flex-1 bg-transparent border-none outline-none text-slate-800 text-sm placeholder-slate-400 focus:ring-0" />

                <button class="w-6 h-6 text-slate-500 flex items-center justify-center hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>

            <button @click="$emit('send')" :disabled="!canSendMessage" class="w-11 h-11 bg-violet-600 rounded-full text-white flex items-center justify-center transition shadow-lg hover:bg-violet-700 disabled:opacity-50">
                <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>

defineProps({
    modelValue: String,
    disabled: Boolean,
    isLoading: Boolean,
    canSendMessage: Boolean,
});

defineEmits(['update:modelValue', 'send']);
</script>
