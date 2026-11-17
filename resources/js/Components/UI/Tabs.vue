<script setup>
import { Link } from '@inertiajs/vue3';

/**
 * Underlined tabs. Each tab: { key, label, count?, href? }.
 * With `href` the tab is a link (separate pages); otherwise it sets v-model.
 */
const props = defineProps({
  tabs: { type: Array, required: true },
  modelValue: { type: String, default: '' },
  label: { type: String, default: 'Sections' },
});
const emit = defineEmits(['update:modelValue']);

const select = (key) => emit('update:modelValue', key);
</script>

<template>
  <div class="border-b border-gray-200">
    <nav class="-mb-px flex gap-6 overflow-x-auto" :aria-label="label" role="tablist">
      <template v-for="tab in tabs" :key="tab.key">
        <component
          :is="tab.href ? Link : 'button'"
          :href="tab.href"
          :type="tab.href ? undefined : 'button'"
          role="tab"
          :aria-selected="modelValue === tab.key"
          @click="!tab.href && select(tab.key)"
          class="flex shrink-0 items-center gap-2 whitespace-nowrap border-b-2 px-1 pb-3 text-sm font-medium transition-colors"
          :class="modelValue === tab.key
            ? 'border-indigo-600 text-indigo-600'
            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
        >
          {{ tab.label }}
          <span v-if="tab.count !== undefined && tab.count !== null"
            class="rounded-full px-2 py-0.5 text-xs"
            :class="modelValue === tab.key ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'">
            {{ tab.count }}
          </span>
        </component>
      </template>
    </nav>
  </div>
</template>
