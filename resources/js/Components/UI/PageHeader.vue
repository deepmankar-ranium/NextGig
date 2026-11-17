<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';

defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  // A URL to go back to (detail pages). Top-level pages don't need one: the nav is there.
  backHref: { type: String, default: '' },
  backLabel: { type: String, default: 'Back' },
});
</script>

<template>
  <div class="mb-6">
    <Link v-if="backHref" :href="backHref" class="mb-3 inline-flex items-center text-sm text-gray-500 hover:text-gray-800">
      <ChevronLeft class="mr-1 h-4 w-4" /> {{ backLabel }}
    </Link>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ title }}</h1>
        <p v-if="subtitle" class="mt-1 text-sm text-gray-600 sm:text-base">{{ subtitle }}</p>
      </div>
      <div v-if="$slots.actions" class="flex flex-wrap gap-2 sm:shrink-0">
        <slot name="actions" />
      </div>
    </div>
    <div v-if="$slots.tabs" class="mt-5">
      <slot name="tabs" />
    </div>
  </div>
</template>
