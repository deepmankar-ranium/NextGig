<template>
  <transition
    enter-active-class="transition ease-out duration-100"
    enter-from-class="transform opacity-0 scale-95"
    enter-to-class="transform opacity-100 scale-100"
    leave-active-class="transition ease-in duration-75"
    leave-from-class="transform opacity-100 scale-100"
    leave-to-class="transform opacity-0 scale-95"
  >
    <div
      v-if="show"
      class="fixed z-10 p-1 bg-white rounded-full shadow-xl border border-slate-200 flex items-center gap-1"
      :style="{ top: `${position.top}px`, left: `${position.left}px` }"
      @mouseenter="$emit('mouseenter')"
      @mouseleave="$emit('mouseleave')"
    >
      <button @click="$emit('reply', message.id)" title="Reply" class="p-1.5 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-100">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
      </button>
      <button @click="$emit('copy', message.id)" title="Copy" class="p-1.5 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-100">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
      </button>
      <button @click="$emit('delete', message.id)" title="Delete" class="p-1.5 rounded-full text-slate-500 hover:text-red-600 hover:bg-red-50">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
      </button>
      <p>{{ message.id }}</p>
    </div>
  </transition>
</template>

<script setup>

defineProps({
  show: Boolean,
  message: Object,
  position: Object,
});

defineEmits(['mouseenter', 'mouseleave', 'reply', 'copy', 'delete']);
</script>
