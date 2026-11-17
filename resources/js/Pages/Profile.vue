<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import AccountTabs from '@/Components/UI/AccountTabs.vue';
import { initials } from '@/utils/nav';
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
    <div>
      <div class="page-narrow">
        <PageHeader title="Account" subtitle="Your sign-in details.">
          <template #tabs><AccountTabs /></template>
        </PageHeader>

        <!-- Profile Card -->
        <div class="card overflow-hidden">
          <div class="p-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex items-center space-x-5">
                    <div class="flex-shrink-0">
                        <div class="relative">
                            <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center text-xl font-semibold text-white">
                                {{ initials(user.full_name) }}
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
