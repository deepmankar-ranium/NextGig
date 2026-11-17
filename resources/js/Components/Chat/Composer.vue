<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import { SendHorizontal, Loader2 } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Write a message…' },
  disabled: Boolean,
  busy: Boolean,
  maxLength: { type: Number, default: 5000 },
  autofocus: Boolean,
});
const emit = defineEmits(['update:modelValue', 'send']);

const el = ref(null);

const resize = () => {
  if (!el.value) return;
  el.value.style.height = 'auto';
  el.value.style.height = `${Math.min(el.value.scrollHeight, 160)}px`;
};

watch(() => props.modelValue, () => nextTick(resize));
onMounted(() => { resize(); if (props.autofocus) el.value?.focus(); });

const canSend = () => !props.disabled && !props.busy && props.modelValue.trim().length > 0;

// Enter sends, Shift+Enter adds a line. Don't send while an IME composition is active.
const onKeydown = (e) => {
  if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
    e.preventDefault();
    if (canSend()) emit('send');
  }
};

defineExpose({ focus: () => el.value?.focus() });
</script>

<template>
  <div class="border-t border-gray-200 bg-white px-3 py-3 sm:px-4">
    <div class="flex items-end gap-2 rounded-2xl border border-gray-300 bg-white py-1.5 pl-4 pr-1.5 shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
      <textarea
        ref="el"
        rows="1"
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        @keydown="onKeydown"
        :placeholder="placeholder"
        :disabled="disabled"
        :maxlength="maxLength"
        aria-label="Message"
        class="max-h-40 flex-1 resize-none border-0 bg-transparent px-0 py-1.5 text-sm text-gray-900 placeholder-gray-400 focus:ring-0"
      ></textarea>
      <button type="button" @click="canSend() && emit('send')" :disabled="!canSend()" aria-label="Send"
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white transition-colors hover:bg-indigo-700 disabled:bg-gray-200 disabled:text-gray-400">
        <Loader2 v-if="busy" class="h-4 w-4 animate-spin" />
        <SendHorizontal v-else class="h-4 w-4" />
      </button>
    </div>
    <p class="mt-1.5 hidden px-1 text-[11px] text-gray-400 sm:block">Enter to send · Shift + Enter for a new line</p>
  </div>
</template>
