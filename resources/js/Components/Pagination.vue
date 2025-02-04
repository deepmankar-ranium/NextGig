<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: {
    type: Array,
    required: true
  }
});
</script>

<template>
  <nav class="mt-6 flex justify-center space-x-1">
    <template v-for="link in links" :key="link.label">
      <Link 
        preserve-scroll
        :href="link.url ?? ''"
        v-html="link.label"
        class="px-4 py-2 text-sm font-medium transition-all rounded-md border"
        :class="{
          'bg-black text-white border-gray-800 shadow-md': link.active,
          'text-gray-700 border-gray-300 hover:bg-gray-100': !link.active && link.url,
          'text-gray-400 cursor-not-allowed border-gray-200': !link.url
        }"
      />
    </template>
  </nav>
</template>
