<template>
  <div class="relative bg-white p-6 mb-4 w-64 rounded-lg shadow-lg">
    <!-- Diagonal Ribbon at Top Right -->
    <div class="absolute top-0 right-0 w-40 h-40 bg-green-500 opacity-20 ribbon-top-right"></div>
   
  </div>
</template>

<script setup>
</script>

<style scoped>
@keyframes ribbonMove {
  0% {
    transform: translateX(-100%) rotate(45deg);
  }
  100% {
    transform: translateX(100%) rotate(45deg);
  }
}

.ribbon-top-right {
  animation: ribbonMove 5s linear infinite;
  position: absolute;
  top: 0;
  right: 0;
  width: 140%;
  height: 140%;
  background: linear-gradient(45deg, rgba(34,197,94,0.6), rgba(74,222,128,0.6));
  transform-origin: center;
  opacity: 0.3;
}
</style>
