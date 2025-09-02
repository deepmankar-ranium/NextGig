<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center sm:py-12">
    <div class="p-10 xs:p-0 mx-auto md:w-full md:max-w-md">
      <h1 class="font-bold text-center text-2xl mb-5">Reset Your Password</h1>
      <div class="bg-white shadow w-full rounded-lg divide-y divide-gray-200">
        <form @submit.prevent="submit">
          <div class="px-5 py-7">
            <input type="hidden" v-model="form.token">
            <div v-if="form.errors.token" class="text-red-500 text-xs italic">{{ form.errors.token }}</div>

            <label class="font-semibold text-sm text-gray-600 pb-1 block">E-mail</label>
            <input v-model="form.email" type="email" class="border rounded-lg px-3 py-2 mt-1 mb-5 text-sm w-full" placeholder="yourname@example.com" required autofocus />
            <div v-if="form.errors.email" class="text-red-500 text-xs italic">{{ form.errors.email }}</div>

            <label class="font-semibold text-sm text-gray-600 pb-1 block">Password</label>
            <input v-model="form.password" type="password" class="border rounded-lg px-3 py-2 mt-1 mb-5 text-sm w-full" required />
            <div v-if="form.errors.password" class="text-red-500 text-xs italic">{{ form.errors.password }}</div>

            <label class="font-semibold text-sm text-gray-600 pb-1 block">Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" class="border rounded-lg px-3 py-2 mt-1 mb-5 text-sm w-full" required />

            <button type="submit" class="transition duration-200 bg-blue-500 hover:bg-blue-600 focus:bg-blue-700 focus:shadow-sm focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 text-white w-full py-2.5 rounded-lg text-sm shadow-sm hover:shadow-md font-semibold text-center inline-block" :disabled="form.processing">
              <span class="inline-block mr-2">Reset Password</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 inline-block">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { watch } from 'vue';

export default {
  props: {
    token: String,
    email: String,
    status: String,
  },
  setup(props) {
    const form = useForm({
      token: props.token,
      email: props.email,
      password: '',
      password_confirmation: '',
    });

    const toast = useToast();

    const submit = () => {
      form.post('/reset-password');
    };

    watch(() => props.status, (newStatus) => {
      if (newStatus) {
        toast.success(newStatus);
        setTimeout(() => {
          router.visit('/login');
        }, 1000);
      }
    });

    return { form, submit };
  },
};
</script>
