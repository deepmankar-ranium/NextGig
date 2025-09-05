<script setup>
import Header from './Header.vue';
import Footer from './Footer.vue';
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <Header />

    <!-- Main Content -->
    <main class="flex-grow">
      <slot></slot>
    </main>

    <Footer />
  </div>
</template>