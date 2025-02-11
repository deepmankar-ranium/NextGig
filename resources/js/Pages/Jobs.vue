<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { BriefcaseIcon, MapPinIcon, CurrencyDollarIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import Pagination from '../Components/Pagination.vue';

const props = defineProps({
  jobListings: {
    type: Object,
    required: true
  }
});

const formatSalary = (salary) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(salary);
};

const truncate = (text, length) => {
  if (!text) return "";
  return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<template>
  <AppLayout>
    <div class="w-full flex justify-between items-center mt-8 px-6">
      <Link href="/Home" class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all bg-white text-gray-700 hover:bg-gray-100 border-gray-300">← Go Back</Link>
      <Link href="/Jobs/create" class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all bg-black text-white hover:bg-gray-900">+ Create Job</Link>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Available Positions</h1>

      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Link 
          v-for="job in props.jobListings.data" 
          :key="job.id" 
          :href="`/Jobs/job/${job.id}`"
          class="bg-white border border-gray-200 shadow-sm rounded-lg transition-all hover:shadow-md">
          
          <div class="p-6">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-lg font-semibold text-gray-900">{{ job.title }}</h2>
              <span class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full">New</span>
            </div>
            <p class="text-gray-600 text-sm mb-4">{{ truncate(job.description, 100) }}</p>

            <div class="space-y-2">
              <div class="flex items-center text-sm text-gray-600">
                <MapPinIcon class="h-5 w-5 mr-2 text-gray-400" />
                Remote
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <CurrencyDollarIcon class="h-5 w-5 mr-2 text-gray-400" />
                {{ formatSalary(job.salary) }} per year
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <BuildingOfficeIcon class="h-5 w-5 mr-2 text-gray-400" />
                {{ job.employer?.name }}
              </div>
            </div>
          </div>

          <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <span class="text-blue-600 font-medium hover:text-blue-700 transition">View Details →</span>
          </div>
        </Link>
      </div>

      <Pagination v-if="props.jobListings.links" :links="props.jobListings.links" class="mt-10" />
    </div>
  </AppLayout>
</template>
