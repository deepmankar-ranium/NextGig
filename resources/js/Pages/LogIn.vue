<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { EyeIcon, EyeOffIcon, MailIcon, LockIcon } from 'lucide-vue-next';
import { router, Link } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false
});

const showPassword = ref(false);

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const submit = () => {
  form.post('/login', {
    onSuccess: () => {
      form.reset('password');
    },
    preserveScroll: true,
  });
};
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-900">
      <div class="w-full sm:max-w-md px-8 py-10 bg-white shadow-lg rounded-lg">
        <h2 class="text-3xl font-extrabold text-center mb-8 text-gray-900">Welcome Back</h2>
        
        <form @submit.prevent="submit" class="space-y-6">
          <div class="relative">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-700 mb-1" for="email">
              Email
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <MailIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.email"
                id="email"
                type="email"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                :class="{ 'border-red-500': form.errors.email }"
                required
              >
            </div>
            <div v-if="form.errors.email" class="mt-1 text-red-600 text-sm">{{ form.errors.email }}</div>
          </div>

          <div class="relative">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-700 mb-1" for="password">
              Password
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.password"
                id="password"
                :type="showPassword ? 'text' : 'password'"
                class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                :class="{ 'border-red-500': form.errors.password }"
                required
              >
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" @click="togglePasswordVisibility" class="focus:outline-none">
                  <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-400" />
                  <EyeOffIcon v-else class="h-5 w-5 text-gray-400" />
                </button>
              </div>
            </div>
            <div v-if="form.errors.password" class="mt-1 text-red-600 text-sm">{{ form.errors.password }}</div>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center">
              <input
                v-model="form.remember"
                type="checkbox"
                class="rounded border-gray-300 text-gray-600 shadow-sm focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-700"
              >
              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
            </label>
            <a href="/forgot-password" class="text-sm text-gray-600 hover:text-gray-500 dark:text-gray-400 dark:hover:text-gray-300">Forgot password?</a>
          </div>

          <div>
            <button
              :disabled="form.processing"
              type="submit"
              class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition">
              <span v-if="!form.processing">Log in</span>
              <svg v-else class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="form.processing">Logging in...</span>
            </button>
          </div>

          <div class="text-sm text-center text-gray-00 dark:text-gray-800">
            Don't have an account? 
            <a href="/register" class="font-medium text-blue-600 hover:text-blue-500  transition duration-150 ease-in-out">Sign up</a>
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
</style>





<!-- <Link href="/" class="text-white cursor-pointer">Home</Link>
<Link href="/Jobs" class="ml-4 text-white cursor-pointer">Jobs</Link>
<Link href="/about" class="ml-4 text-white cursor-pointer">About</Link>
<Link href="/contact" class="ml-4 text-white cursor-pointer">Contact</Link> -->