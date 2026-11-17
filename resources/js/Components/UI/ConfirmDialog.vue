<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Confirm' },
  danger: Boolean,
});
const emit = defineEmits(['confirm', 'cancel']);

const onKey = (e) => { if (e.key === 'Escape') emit('cancel'); };
onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click="emit('cancel')">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" role="alertdialog" aria-modal="true" :aria-label="title" @click.stop>
      <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
      <p v-if="message" class="mt-2 text-sm text-gray-600">{{ message }}</p>
      <div class="mt-6 flex justify-end gap-2">
        <button type="button" class="btn-secondary" @click="emit('cancel')">Cancel</button>
        <button type="button" :class="danger ? 'btn-danger' : 'btn-primary'" @click="emit('confirm')">{{ confirmLabel }}</button>
      </div>
    </div>
  </div>
</template>
