<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center sm:py-12">
    <div class="p-10 xs:p-0 mx-auto md:w-full md:max-w-md">
      <h1 class="font-bold text-center text-2xl mb-5">Forgot Your Password?</h1>
      <div class="bg-white shadow w-full rounded-lg divide-y divide-gray-200">
        <form @submit.prevent="submit">
          <div class="px-5 py-7">
            <p class="text-gray-600 text-sm mb-4">
              No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
            </p>
            <label class="font-semibold text-sm text-gray-600 pb-1 block">E-mail</label>
            <input v-model="form.email" type="email" class="border rounded-lg px-3 py-2 mt-1 mb-5 text-sm w-full" placeholder="yourname@example.com" required autofocus />
            <div v-if="form.errors.email" class="text-red-500 text-xs italic">{{ form.errors.email }}</div>

            <button type="submit" class="transition duration-200 bg-blue-500 hover:bg-blue-600 focus:bg-blue-700 focus:shadow-sm focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 text-white w-full py-2.5 rounded-lg text-sm shadow-sm hover:shadow-md font-semibold text-center inline-block" :disabled="form.processing">
              <span class="inline-block mr-2">Email Password Reset Link</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 inline-block">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </button>
          </div>
        </form>
        <div class="py-5">
          <div class="grid grid-cols-2 gap-1">
            <div class="text-center sm:text-left whitespace-nowrap">
              <a href="/login" class="transition duration-200 mx-5 px-5 py-4 cursor-pointer font-normal text-sm rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-200 focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 ring-inset">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 inline-block align-text-top">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="inline-block ml-1">Back to login</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { watch, computed } from 'vue';

const page = usePage();
const status = computed(() => page.props.flash.status);

const form = useForm({
  email: '',
});

const toast = useToast();

const submit = () => {
  form.post('/send-reset-link', {
    onFinish: () => form.reset('email'),
  });
};

watch(status, (newStatus) => {
  if (newStatus) {
    toast.success(newStatus);
  }
});
</script>
