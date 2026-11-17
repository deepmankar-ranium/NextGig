<script setup>
defineProps({
  icon: { type: [Object, Function], default: null },
  title: { type: String, required: true },
  text: { type: String, default: '' },
});
</script>

<template>
  <div class="card card-body flex flex-col items-center py-12 text-center">
    <div v-if="icon" class="mb-3 rounded-full bg-indigo-50 p-3">
      <component :is="icon" class="h-6 w-6 text-indigo-600" />
    </div>
    <h3 class="text-sm font-semibold text-gray-900">{{ title }}</h3>
    <p v-if="text" class="mt-1 max-w-sm text-sm text-gray-500">{{ text }}</p>
    <div v-if="$slots.default" class="mt-5"><slot /></div>
  </div>
</template>
