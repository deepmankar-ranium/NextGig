<template>
    <AppLayout>
      <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 sm:px-6 lg:px-8">
          
          <div v-if="isEmployer" class="mt-4 mb-6 flex justify-between">
            <p class="text-3xl text-gray-900 font-semibold">Here are your job postings:</p>
            <Link href="/view-applications">
                <a class="inline-block px-6 py-3 bg-gray-800 text-white font-semibold text-sm rounded-lg shadow-md hover:bg-white hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-800 transition duration-200 ease-in-out">
                  View Your Jobs Applications
                </a>
              </Link>
          </div>
  
        <!-- Job Listings Grid -->
        <div  class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Link 
              v-for="job in jobListings.data" 
              :key="job.id" 
              :href="`/Jobs/job/${job.id}`"
            >
            <!-- Higher order component -->
              <withRecentlyPostedBanner :job="job">
                <JobCard :job="job" />
              </withRecentlyPostedBanner>
            </Link>
          </div>
  
          
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import AppLayout from '@/Layout/AppLayout.vue';
  import JobCard from '@/Components/JobCard.vue';
  import withRecentlyPostedBanner from '@/Components/withRecentlyPostedBanner.vue';
import { Link } from '@inertiajs/vue3';
  const props = defineProps({
    jobListings: Object,
    isEmployer: Boolean,
  });
  
  const goToPage = (pageNumber) => {
    // Update the page in your API call to reflect the pagination changes
    // This could either be done using Vue Router, or by updating an API call
    console.log(`Navigating to page ${pageNumber}`);
  };
  </script>
  
  <style scoped>
  /* You can add custom styles here */
  </style>
  