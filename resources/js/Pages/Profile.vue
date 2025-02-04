<template>
    <AppLayout>
    <div class="max-w-2xl mx-auto p-6">
      <div class="bg-white shadow-lg rounded-lg overflow-hidden p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Profile</h1>
  
        <div class="space-y-4">
          <p class="text-gray-600 text-lg">
            <span class="font-semibold text-gray-900">Name:</span> 
            {{ $page.props.auth.user.full_name }}
          </p>
          <p class="text-gray-600 text-lg">
            <span class="font-semibold text-gray-900">Email:</span> 
            {{ $page.props.auth.user.email }}
          </p>
        </div>
  
        <div class="mt-8">
          <button 
            @click="showLogoutConfirm = true"
            class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition">
            Logout
          </button>
        </div>

        <!-- Logout Confirmation Modal -->
        <div v-if="showLogoutConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
          <div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-xl">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Confirm Logout</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to logout?</p>
            <div class="flex space-x-4">
              <button
                @click="logout"
                class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition">
                Yes, Logout
              </button>
              <button
                @click="showLogoutConfirm = false"
                class="flex-1 border border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { router } from '@inertiajs/vue3'
  import AppLayout from '@/Layout/AppLayout.vue';

  const showLogoutConfirm = ref(false);
  
  const logout = () => {
    router.post('/logout');
    showLogoutConfirm.value = false;
  }
  </script>
