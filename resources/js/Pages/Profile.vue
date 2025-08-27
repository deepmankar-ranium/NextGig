<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-8">
      <div class="bg-white shadow-xl rounded-2xl overflow-hidden flex flex-col md:flex-row">
        <!-- Profile Sidebar -->
        <div class="bg-gray-900 flex flex-col items-center justify-center md:w-1/3 py-10 px-6">
          <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center mb-4 border-4 border-white shadow">
            <span class="text-3xl font-bold text-gray-900">
              {{ user.full_name ? user.full_name.charAt(0) : '' }}
            </span>
          </div>
          <h2 class="text-xl font-semibold text-white mb-1">{{ user.full_name }}</h2>
          <p class="text-gray-300 text-sm">{{ user.email }}</p>
          <span class="mt-2 inline-block bg-white text-gray-900 text-xs font-semibold px-3 py-1 rounded-full shadow">
            {{ user.role.name }}
          </span>
        </div>
        <!-- Profile Details -->
        <div class="flex-1 p-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-8 border-b pb-4">Profile Details</h1>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <div class="text-gray-500 text-xs uppercase font-semibold mb-1">Name</div>
              <div class="text-lg text-gray-900 font-medium">{{ user.full_name }}</div>
            </div>
            <div>
              <div class="text-gray-500 text-xs uppercase font-semibold mb-1">Email</div>
              <div class="text-lg text-gray-900 font-medium">{{ user.email }}</div>
            </div>
            <div>
              <div class="text-gray-500 text-xs uppercase font-semibold mb-1">Role</div>
              <div class="text-lg text-gray-900 font-medium">{{ user.role.name }}</div>
            </div>
          </div>
          <div class="mt-10 flex justify-end">
            <button 
              @click="showLogoutConfirm = true"
              class="border bg-gray-900 text-white border-white px-8 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-900 transition duration-300"
            >
              Logout
            </button>
          </div>
        </div>
      </div>

      <!-- Logout Confirmation Modal -->
      <div v-if="showLogoutConfirm" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl p-8 max-w-sm w-full shadow-2xl">
          <h3 class="text-2xl font-semibold text-gray-900 mb-4">Confirm Logout</h3>
          <p class="text-gray-600 mb-8">Are you sure you want to logout?</p>
          <div class="flex space-x-4">
            <button
              @click="logout"
              class="flex-1 border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-900 transition duration-300"
            >
              Yes, Logout
            </button>
            <button
              @click="showLogoutConfirm = false"
              class="flex-1 border border-gray-300 text-gray-900 px-6 py-3 rounded-lg shadow-md font-medium hover:bg-gray-100 transition duration-300"
            >
              Cancel
            </button>
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
const props = defineProps({
  user: Object
})

const logout = () => {
  router.post('/logout');
  showLogoutConfirm.value = false;
}
</script>

<style scoped>
/* Custom styles for improved layout */
</style>
