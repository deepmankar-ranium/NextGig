<template>
  <div class="h-full bg-gray-800 border-r border-gray-700 flex flex-col text-gray-200">
    <!-- Header with search -->
    <div class="p-4 border-b border-gray-700">
      <div class="bg-gray-700 rounded-lg p-3 flex items-center gap-3">
        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-100">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            Inbox
        </h2>

      </div>
    </div>

    <!-- Recent section -->
    <div class="flex items-center justify-between p-4 border-b border-gray-700">
      <div class="flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
        </svg>
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Conversations</h3>
      </div>
      <span class="bg-gray-800 text-gray-300 text-xs font-semibold px-2 py-0.5 rounded-full">{{ users.length }}</span>
    </div>

    <!-- User List -->
    <div class="flex-1 overflow-y-auto p-2">
      <button
        v-for="user in users"
        :key="user.id"
        @click="$emit('user-selected', user)"
        :class="{
          'bg-indigo-700': user.id === selectedUser?.id,
          'hover:bg-gray-700': user.id !== selectedUser?.id,
          'w-full flex items-center gap-3 p-3 rounded-lg text-left cursor-pointer transition-all duration-200': true
        }"
      >
        <!-- Avatar -->
        <div class="relative flex-shrink-0">
          <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center font-medium text-sm shadow-lg">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <!-- Online indicator -->
          <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-gray-800 rounded-full"></div>
        </div>

        <!-- User Info -->
        <div class="flex-1 min-w-0">
          <div class="font-medium text-lg mb-1 truncate text-gray-100">
            {{ user.name }}
          </div>
        </div>
      </button>
    </div>

    <!-- Return to Home Page Button -->
    <div class="p-4 border-t border-gray-700 mt-auto">
      <a href="/Home" class="w-full flex items-center justify-center gap-2 p-3 rounded-lg bg-indigo-600 text-white font-medium transition-colors duration-200 hover:bg-indigo-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2 2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        Return to Home
      </a>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  users: {
    type: Array,
    required: true,
  },
  selectedUser: {
    type: Object,
    default: null,
  },
});

defineEmits(['user-selected']);
</script>
