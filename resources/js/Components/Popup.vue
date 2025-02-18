<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
      <button @click="$emit('close')" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900">
        &times;
      </button>
      <div class="mt-4">
        <p>{{ content }}</p>
      </div>
      <div class="mt-4 flex justify-end">
        <button @click="$emit('close')" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  content: {
    type: String,
    required: true
  }
});
</script>

<style scoped>
/* Add any additional styles for the popup here */
</style> 