<template>
    <div class="relative">
      <!-- Ribbon -->
      <div v-if="isRecentlyPosted" class="ribbon">
        <span>New</span>
      </div>
      
      <!-- Slot content -->
      <slot />
    </div>
  </template>
  
  <script setup>
  import { defineProps, computed } from 'vue';
  
  const props = defineProps({
    job: Object,
  });
  
  // Computed property to check if the job was posted within the last 3 days
  const isRecentlyPosted = computed(() => {
    const createdDate = new Date(props.job.created_at);
    const today = new Date();
    const threeDaysAgo = new Date(today.getTime() - 3 * 24 * 60 * 60 * 1000);
    return createdDate >= threeDaysAgo && createdDate <= today;
  });
  </script>
  
  <style scoped>
  .relative {
    position: relative;
    overflow: hidden;
  }
  
  .ribbon {
    position: absolute;
    top: -10px;
    right: -10px;
    width: 100px;
    height: 100px;
    overflow: hidden;
  }
  
  .ribbon span {
    position: absolute;
    top: 19px;
    right: -21px;
    width: 100px;
    padding: 6px 0;
    background-color: #2ecc71;
    color: #fff;
    font: 700 12px/1 'Lato', sans-serif;
    text-shadow: 0 1px 1px rgba(0,0,0,.2);
    text-transform: uppercase;
    text-align: center;
    transform: rotate(45deg);
    box-shadow: 0 2px 4px rgba(0,0,0,.25);
  }
  
  .ribbon span::before,
  .ribbon span::after {
    content: "";
    position: absolute;
    top: 100%;
    z-index: -1;
    border: 5px solid #1e8449;
  }
  
  .ribbon span::before {
    left: 0;
    border-left-color: transparent;
    border-right-color: #1e8449;
  }
  
  .ribbon span::after {
    right: 0;
    border-left-color: #1e8449;
    border-right-color: transparent;
  }
  </style>