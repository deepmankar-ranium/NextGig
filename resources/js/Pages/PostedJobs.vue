<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import JobCard from '@/Components/JobCard.vue';
import withRecentlyPostedBanner from '@/Components/withRecentlyPostedBanner.vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeftIcon, PlusIcon } from 'lucide-vue-next'; // Using lucide for icons

const props = defineProps({
  jobListings: Object,
  isEmployer: Boolean,
});

const handleBack = () => {
  window.history.back();
};
</script>

<template>
  <AppLayout>
    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Page Header -->
        <div class="md:flex md:items-center md:justify-between pb-8 border-b border-gray-200">
          <div class="min-w-0 flex-1">
             <button @click="handleBack" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
              <ChevronLeftIcon class="h-5 w-5 mr-1" />
              Back
            </button>
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
              Your Job Postings
            </h1>
            <p class="mt-2 text-lg text-gray-600">
              Manage your active job listings and view applications.
            </p>
          </div>
          <div class="mt-6 flex space-x-3 md:mt-0 md:ml-4">
            <Link
              href="/Jobs/create"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
              Post a New Job
            </Link>
          </div>
        </div>

        <!-- Secondary Actions -->
        <div class="mt-8 flex flex-wrap gap-4 items-center justify-end">
            <Link
                href="/tags"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
                Manage Tags
            </Link>
            <Link 
                href="/view-applications" 
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gray-800 hover:bg-gray-900"
            >
                View All Applications
            </Link>
        </div>

        <!-- Job Listings Grid -->
        <div v-if="jobListings.data && jobListings.data.length > 0" class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <Link 
            v-for="job in jobListings.data" 
            :key="job.id" 
            :href="`/Jobs/job/${job.id}`"
            class="block"
          >
            <withRecentlyPostedBanner :job="job">
              <JobCard :job="job" />
            </withRecentlyPostedBanner>
          </Link>
        </div>
        
        <!-- Empty State -->
        <div v-else class="mt-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No job postings</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by posting a new job.</p>
            <div class="mt-6">
                <Link
                    href="/Jobs/create"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                    Post a New Job
                </Link>
            </div>
        </div>
          
      </div>
    </div>
  </AppLayout>
</template>
  