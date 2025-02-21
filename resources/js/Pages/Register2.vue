<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { EyeIcon, EyeOffIcon, UserIcon, MailIcon, LockIcon } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';



const form = useForm({
  full_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '' 
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const togglePasswordVisibility = (field) => {
  if (field === 'password') {
    showPassword.value = !showPassword.value;
  } else {
    showConfirmPassword.value = !showConfirmPassword.value;
  }
};

const submit = () => {
 
  form.post('/register-2/register', { preserveScroll: true });
};
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-900 py-20">
    <div class="w-full sm:max-w-md px-8 py-10 bg-white shadow-lg rounded-lg">
      <div class="mb-8 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900">Create an Account</h2>
        <p class="mt-2 text-sm text-gray-500">Join our community today</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Full Name -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-600" for="full_name">Full Name</label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <UserIcon class="h-5 w-5 text-gray-700" />
            </div>
            <input
              v-model="form.full_name"
              id="full_name"
              type="text"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
              :class="{ 'border-red-500': form.errors.full_name }"
              required
            />
          </div>
          <div v-if="form.errors.full_name" class="mt-1 text-red-600 text-sm">{{ form.errors.full_name }}</div>
        </div>

        <!-- Email -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-600" for="email">Email Address</label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MailIcon class="h-5 w-5 text-gray-700" />
            </div>
            <input
              v-model="form.email"
              id="email"
              type="email"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
              :class="{ 'border-red-500': form.errors.email }"
              required
            />
          </div>
          <div v-if="form.errors.email" class="mt-1 text-red-600 text-sm">{{ form.errors.email }}</div>
        </div>

        <!-- Password -->
        <div class="relative">
          <label class="block text-sm font-medium text-gray-600" for="password">Password</label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <LockIcon class="h-5 w-5 text-gray-700" />
            </div>
            <input
              v-model="form.password"
              id="password"
              :type="showPassword ? 'text' : 'password'"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
              :class="{ 'border-red-500': form.errors.password }"
              required
            />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <button type="button" @click="togglePasswordVisibility('password')" class="focus:outline-none">
                <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-700" />
                <EyeOffIcon v-else class="h-5 w-5 text-gray-700" />
              </button>
            </div>
          </div>
          <div v-if="form.errors.password" class="mt-1 text-red-600 text-sm">{{ form.errors.password }}</div>
        </div>
        <div class="relative">
          <label class="block text-sm font-medium text-gray-600" for="password_confirmation">
            Confirm Password
          </label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <LockIcon class="h-5 w-5 text-gray-700" />
            </div>
            <input
              v-model="form.password_confirmation"
              id="password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
              required
            >
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <button type="button" @click="togglePasswordVisibility('confirm')" class="focus:outline-none">
                <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5 text-gray-700" />
                <EyeOffIcon v-else class="h-5 w-5 text-gray-700" />
              </button>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="max-w-md mx-auto p-6">
          <div class="flex flex-col space-y-4">
            <!-- Primary Sign Up Button -->
            <button
              :disabled="isProcessing"
              @click="handleSubmit"
              class="w-full bg-gray-900 text-white px-6 py-3 rounded-lg shadow-md font-medium
                     hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
                     disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200
                     flex items-center justify-center"
            >
              <template v-if="!isProcessing">
                Sign Up
              </template>
              <template v-else>
                <svg
                  class="animate-spin h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                  />
                </svg>
                <span class="ml-2">Processing...</span>
              </template>
            </button>
      
            <!-- Divider -->
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or continue with</span>
              </div>
            </div>
      
            <!-- Google Sign In Button -->
            <a
              href="/auth/google"
              class="flex items-center justify-center w-full px-6 py-3 border border-gray-300 
                     rounded-lg shadow-sm bg-white text-gray-700 font-medium
                     hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500
                     transition-all duration-200"
            >
              <svg class="w-5 h-5 mr-3" viewBox="0 0 48 48">
                <path
                  fill="#4285F4"
                  d="M24 9.5c3.4 0 6.5 1.2 9 3.6l6.7-6.7C34.8 2 29.7 0 24 0 14.8 0 6.9 5.6 3 13.8l7.9 6.2C13.1 13.4 18.1 9.5 24 9.5z"
                />
                <path
                  fill="#34A853"
                  d="M46.5 24.5c0-1.6-.2-3.2-.5-4.6H24v9.3h13c-.6 3-2.2 5.5-4.7 7.2l7.3 5.7c4.2-3.9 6.9-9.7 6.9-17.6z"
                />
                <path
                  fill="#FBBC05"
                  d="M11.1 28.5c-1.4-2-2.1-4.4-2.1-7s.8-5 2.1-7L3 8.2C.8 12.2 0 17 0 24s.8 11.8 3 15.8l8.1-6.3z"
                />
                <path
                  fill="#EA4335"
                  d="M24 48c6.5 0 12-2.2 16-5.8l-7.3-5.7c-2.2 1.5-5 2.4-8.7 2.4-5.9 0-10.9-4-12.8-9.4l-8.1 6.3C7 42.4 14.8 48 24 48z"
                />
              </svg>
              <span>Continue with Google</span>
            </a>
          </div>
          <div class="text-sm text-center mt-3">
            <span class="text-gray-600 dark:text-gray-800">
              Already Registered?
            </span>
            <a 
              href="/login" 
              class="ml-1 font-medium text-blue-600 hover:text-blue-500 
                     focus:outline-none focus:underline
                     transition duration-150 ease-in-out"
            >
             Log In
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
          

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: trangrayY(-10px); }
  to { opacity: 1; transform: trangrayY(0); }
}

form > div {
  animation: fadeIn 0.5s ease-out forwards;
}

form > div:nth-child(1) { animation-delay: 0.1s; }
form > div:nth-child(2) { animation-delay: 0.2s; }
form > div:nth-child(3) { animation-delay: 0.3s; }
form > div:nth-child(4) { animation-delay: 0.4s; }
form > div:nth-child(5) { animation-delay: 0.5s; }
form > div:nth-child(6) { animation-delay: 0.6s; }
</style>