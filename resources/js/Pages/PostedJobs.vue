<template>
    <AppLayout>
      <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 sm:px-6 lg:px-8">
          
          <div  class="mt-4 mb-6 ">
            <p class="text-3xl text-gray-900 font-semibold">Here are your job postings:</p>
            <div class="flex items-center justify-between mt-10">
      
            <Link
            href="/tags"
            class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all
                   bg-white text-gray-700 hover:bg-gray-100 border-gray-300">
          View Tags
          </Link>
            <Link href="/view-applications">
                <a class="inline-block px-6 py-3 bg-gray-800 text-white font-semibold text-sm rounded-lg shadow-md hover:bg-white hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-800 transition duration-200 ease-in-out">
                  View Your Jobs Applications
                </a>
              </Link>
              </div>
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

  });
  

  </script>
  
  <style scoped>
  /* You can add custom styles here */
  </style>
  