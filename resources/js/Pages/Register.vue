<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { EyeIcon, EyeOffIcon, UserIcon, MailIcon, LockIcon,InfoIcon } from 'lucide-vue-next';
import { router, Link } from '@inertiajs/vue3';

const form = useForm({
  full_name: '',
  email: '',
  password: '',
  password_confirmation: ''
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

  form.post('/register', {
      preserveScroll: true,
    });
  
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
          <div class="relative">
            <label class="block text-sm font-medium text-gray-600" for="full_name">
              Full Name
            </label>
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
              >
            </div>
            <div v-if="form.errors.full_name" class="mt-1 text-red-600 text-sm">{{ form.errors.full_name }}</div>
          </div>

          <div class="relative">
            <label class="block text-sm font-medium text-gray-600" for="email">
              Email Address
            </label>
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
              >
            </div>
            <div v-if="form.errors.email" class="mt-1 text-red-600 text-sm">{{ form.errors.email }}</div>
          </div>

          <div class="relative">
            <label class="block text-sm font-medium text-gray-600" for="password">
              Password
            </label>
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
              >
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

          <div>
            <button
              :disabled="form.processing"
              type="submit"
           class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition">
              <span v-if="!form.processing">Sign Up</span>
              <svg v-else class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="form.processing">Signing Up...</span>
            </button>
          </div>

          <div class="text-sm text-center text-gray-500">
            Already have an account? 
            <Link href="/login" class="font-medium text-blue-600 hover:text-blue-500 transition duration-150 ease-in-out">Sign in</Link>
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