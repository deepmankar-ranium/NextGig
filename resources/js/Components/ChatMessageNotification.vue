<template>
    <!-- Avatar circle -->
    <div class="w-8 h-8 bg-green-500 text-white flex items-center justify-center rounded-full shrink-0">
      {{ user[0].toUpperCase() }}
    </div>

    <!-- Message -->
    <div>
      <p class="font-semibold text-gray-800">{{ user }}</p>
      <p class="text-gray-700 text-sm">{{ message }}</p>
    </div>
</template>

<script setup>
defineProps({
  user: String,
  message: String,
});
</script>

<style>
.Vue-Toastification__toast--default {
    background-color: transparent !important;
}
.Vue-Toastification__toast {
    padding: 0 !important;
}
</style>
