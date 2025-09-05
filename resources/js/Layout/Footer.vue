<script setup>
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="bg-white border-t">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div class="text-center text-sm text-gray-500">
        <p>&copy; {{ currentYear }} NextGig. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>
