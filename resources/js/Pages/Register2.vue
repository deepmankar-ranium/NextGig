<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { EyeIcon, EyeOffIcon, UserIcon, MailIcon, LockIcon } from 'lucide-vue-next';

const form = useForm({
  full_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '' // This probably comes from the previous step, might need to be passed as a prop.
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
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="text-center text-3xl font-bold text-indigo-600">
        NextGig
      </h2>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Create your account
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Or
        <Link href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
          sign in to your existing account
        </Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-xl rounded-xl sm:px-10">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Full Name -->
          <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <div class="mt-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <UserIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.full_name"
                id="full_name"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.full_name }"
                required
                placeholder="John Doe"
              />
            </div>
            <div v-if="form.errors.full_name" class="mt-1 text-red-600 text-sm">{{ form.errors.full_name }}</div>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <div class="mt-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <MailIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.email"
                id="email"
                type="email"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.email }"
                required
                placeholder="you@example.com"
              />
            </div>
            <div v-if="form.errors.email" class="mt-1 text-red-600 text-sm">{{ form.errors.email }}</div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="mt-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.password"
                id="password"
                :type="showPassword ? 'text' : 'password'"
                class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.password }"
                required
                placeholder="••••••••"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" @click="togglePasswordVisibility('password')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                  <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                  <EyeOffIcon v-else class="h-5 w-5" />
                </button>
              </div>
            </div>
            <div v-if="form.errors.password" class="mt-1 text-red-600 text-sm">{{ form.errors.password }}</div>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <div class="mt-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="form.password_confirmation"
                id="password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                required
                placeholder="••••••••"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" @click="togglePasswordVisibility('confirm')" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                  <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" />
                  <EyeOffIcon v-else class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors"
            >
              Sign Up
            </button>
          </div>
        </form>

        <!-- Divider -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">Or continue with</span>
            </div>
          </div>

          <!-- Social Login -->
          <div class="mt-6">
            <a
              href="/auth/google"
              class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              <span class="sr-only">Sign up with Google</span>
              <svg class="w-5 h-5" viewBox="0 0 48 48">
                <path fill="#4285F4" d="M24 9.5c3.4 0 6.5 1.2 9 3.6l6.7-6.7C34.8 2 29.7 0 24 0 14.8 0 6.9 5.6 3 13.8l7.9 6.2C13.1 13.4 18.1 9.5 24 9.5z" />
                <path fill="#34A853" d="M46.5 24.5c0-1.6-.2-3.2-.5-4.6H24v9.3h13c-.6 3-2.2 5.5-4.7 7.2l7.3 5.7c4.2-3.9 6.9-9.7 6.9-17.6z" />
                <path fill="#FBBC05" d="M11.1 28.5c-1.4-2-2.1-4.4-2.1-7s.8-5 2.1-7L3 8.2C.8 12.2 0 17 0 24s.8 11.8 3 15.8l8.1-6.3z" />
                <path fill="#EA4335" d="M24 48c6.5 0 12-2.2 16-5.8l-7.3-5.7c-2.2 1.5-5 2.4-8.7 2.4-5.9 0-10.9-4-12.8-9.4l-8.1 6.3C7 42.4 14.8 48 24 48z" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Keeping the fade-in animation as it's a nice touch for a form */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

form > div {
  /* This is a bit broad, let's make it more specific if needed, but for now it's ok */
  animation: fadeIn 0.5s ease-out forwards;
  opacity: 0; /* Start hidden */
}

form > div:nth-child(1) { animation-delay: 0.1s; }
form > div:nth-child(2) { animation-delay: 0.2s; }
form > div:nth-child(3) { animation-delay: 0.3s; }
form > div:nth-child(4) { animation-delay: 0.4s; }
form > div:nth-child(5) { animation-delay: 0.5s; }
</style>
