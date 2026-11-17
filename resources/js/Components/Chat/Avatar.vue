<script setup>
import { computed, ref, watch } from 'vue';
import { initials } from '@/utils/nav';
import { avatarColor } from '@/utils/chat';

const props = defineProps({
  id: { type: [Number, String], default: 0 },
  name: { type: String, default: '' },
  src: { type: String, default: null }, // profile photo URL; falls back to initials
  size: { type: String, default: 'md' }, // xs | sm | md | lg | xl
});
const sizeClass = computed(() => ({
  xs: 'h-8 w-8 text-xs', sm: 'h-8 w-8 text-xs', md: 'h-10 w-10 text-sm', lg: 'h-12 w-12 text-base', xl: 'h-24 w-24 text-3xl',
}[props.size]));

// If the image fails to load (deleted, offline), show initials instead of a broken image.
const failed = ref(false);
watch(() => props.src, () => { failed.value = false; });
</script>

<template>
  <img v-if="src && !failed" :src="src" alt="" loading="lazy" @error="failed = true"
    class="shrink-0 rounded-full bg-gray-100 object-cover" :class="sizeClass" />
  <span v-else class="flex shrink-0 items-center justify-center rounded-full font-semibold text-white" :class="[sizeClass, avatarColor(id)]" aria-hidden="true">
    {{ initials(name) }}
  </span>
</template>
