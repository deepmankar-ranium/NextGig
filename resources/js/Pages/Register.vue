<template>
  <div class="flex items-center justify-center h-screen bg-gray-900">
    <div class="bg-gray-800/80 backdrop-blur-xl p-10 rounded-2xl shadow-2xl w-[420px] border border-gray-700">

      <!-- Branding / Logo -->
      <div class="flex justify-center mb-6">
        <h2 class="text-4xl font-extrabold text-white">
            NextGig
        </h2>
      </div>

      <!-- Title -->
      <h2 class="text-2xl font-bold text-center text-white mb-2">
        Create your account
      </h2>
      <p class="text-gray-400 text-sm text-center mb-8">
        Choose your role to continue with registration
      </p>

      <!-- Form -->
      <form @submit.prevent="form.post('/register/store-role')" class="space-y-6">

        <!-- Role Selection -->
        <div>
          <label class="block text-gray-300 text-sm font-medium mb-2">
            Select your role
          </label>
          <select
            v-model="form.role_id"
            class="w-full px-4 py-3 bg-gray-900 text-white text-sm rounded-lg border border-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
          >
            <option disabled value="">-- Select a role --</option>
            <option
              v-for="role in roles"
              :key="role.id"
              :value="role.id"
              class="bg-gray-900 text-white"
            >
              {{ role.name }}
            </option>
          </select>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          class="w-full py-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold tracking-wide transition"
          :disabled="form.processing"
        >
          Continue
        </button>
      </form>

      <!-- Already Registered -->
      <p class="text-gray-400 text-sm text-center mt-8">
        Already have an account?
        <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-medium transition">
          Sign in
        </a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ roles: Array });

const form = useForm({
  role_id: '',
});
</script>
