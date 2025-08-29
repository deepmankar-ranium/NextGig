<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeftIcon, User, Mail, Briefcase, Edit } from 'lucide-vue-next';

const props = defineProps({
  user: Object
});

const handleBack = () => {
  window.history.back();
};
</script>

<template>
  <AppLayout>
    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Page Header -->
        <div class="pb-8 border-b border-gray-200">
          <button @click="handleBack" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
            <ChevronLeftIcon class="h-5 w-5 mr-1" />
            Back
          </button>
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Account Profile
          </h1>
          <p class="mt-2 text-lg text-gray-600">
            View and manage your profile details.
          </p>
        </div>

        <!-- Profile Card -->
        <div class="mt-8 bg-white rounded-xl shadow-md overflow-hidden">
          <div class="p-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex items-center space-x-5">
                    <div class="flex-shrink-0">
                        <div class="relative">
                            <div class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                                <User class="h-12 w-12 text-gray-500" />
                            </div>
                        </div>
                    </div>
                    <div class="pt-1.5">
                        <h2 class="text-2xl font-bold text-gray-900">{{ user.full_name }}</h2>
                        <p class="text-sm font-medium text-gray-500">{{ user.email }}</p>
                    </div>
                </div>
                <!-- <div class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-reverse sm:space-y-0 sm:space-x-3 md:mt-0 md:flex-row md:space-x-3">
                    <Link href="/profile/edit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <Edit class="h-5 w-5 mr-2" />
                        Edit Profile
                    </Link>
                </div> -->
            </div>
          </div>

          <!-- Profile Details -->
          <div class="border-t border-gray-200 px-8 py-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
              <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500 flex items-center">
                    <User class="h-5 w-5 mr-2 text-gray-400" />
                    Full Name
                </dt>
                <dd class="mt-1 text-lg text-gray-900">{{ user.full_name }}</dd>
              </div>
              <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500 flex items-center">
                    <Mail class="h-5 w-5 mr-2 text-gray-400" />
                    Email address
                </dt>
                <dd class="mt-1 text-lg text-gray-900">{{ user.email }}</dd>
              </div>
              <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500 flex items-center">
                    <Briefcase class="h-5 w-5 mr-2 text-gray-400" />
                    Account Role
                </dt>
                <dd class="mt-1 text-lg text-gray-900 capitalize">{{ user.role.name }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
