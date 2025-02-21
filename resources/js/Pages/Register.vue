<template>
    <div class="flex items-center justify-center h-screen bg-gray-900">
      <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-semibold mb-4 text-center">Select Your Role</h2>
        <form @submit.prevent="form.post('/register/store-role')">
          <label class="block text-gray-600 font-medium mb-2">Choose your role:</label>
          <select v-model="form.role_id" class="w-full p-2 border rounded mb-4">
            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
          </select>
          <button type="submit" class="w-full bg-gray-700 text-white py-2 rounded" :disabled="form.processing">
            Continue with registration
          </button>
          
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import { useForm } from '@inertiajs/vue3';
  
  defineProps({ roles: Array });
  
  const form = useForm({
    role_id: '',
  });

  </script>
  