<script setup>
import { computed } from 'vue';
import { initials } from '@/utils/nav';
import { avatarColor } from '@/utils/chat';

const props = defineProps({
  id: { type: [Number, String], default: 0 },
  name: { type: String, default: '' },
  size: { type: String, default: 'md' }, // sm | md | lg
});
const sizeClass = computed(() => ({ sm: 'h-8 w-8 text-xs', md: 'h-10 w-10 text-sm', lg: 'h-12 w-12 text-base' }[props.size]));
</script>

<template>
  <span class="flex shrink-0 items-center justify-center rounded-full font-semibold text-white" :class="[sizeClass, avatarColor(id)]" aria-hidden="true">
    {{ initials(name) }}
  </span>
</template>
