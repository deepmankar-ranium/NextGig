<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
const props =defineProps({
  jobListings: Object
})

</script>

<template>
  <AppLayout>
    <!-- Hero Section -->
    <section class="relative bg-gray-900 text-white text-center py-20 px-6">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-5xl font-extrabold leading-tight">Find Your Dream Job Today</h1>
        <p class="mt-4 text-lg opacity-90">
          Explore thousands of job listings and connect with top employers.
        </p>
        <div class="mt-6 flex justify-center space-x-4">
          <Link href="/Jobs" class="bg-white text-gray-600 px-6 py-3 rounded-lg shadow-md font-medium hover:bg-gray-200 transition">
            Browse Jobs
          </Link>
          <Link href="/contact" class="border border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition">
            Contact Us
          </Link>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="max-w-7xl mx-auto py-16 px-6 space-y-16">
      <!-- Section Title -->
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900">Why Choose Us?</h2>
        <p class="mt-3 text-gray-600 text-2xl">We help job seekers and employers connect efficiently.</p>
      </div>
    
      <!-- Featured Jobs Heading -->
      <h2 class="text-4xl font-bold text-gray-900 mt-10 mb-6 text-center">Featured Jobs</h2>
    
      <!-- Jobs Grid -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div 
          v-for="(job, id) in jobListings.data" 
          :key="id"
          class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition-shadow duration-300 ease-in-out"
        >
          <h3 class="text-lg font-semibold text-gray-900">{{ job.title }}</h3>
          <p class="text-green-600 font-semibold mt-1">{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(job.salary) }} per year</p>
          <p class="mt-3 text-gray-600 line-clamp-3">
            {{ job.description.length > 120 ? job.description.substring(0, 120) + '...' : job.description }}
          </p>
          <Link 
            :href="`/Jobs/job/${job.id}`" 
            class="inline-block mt-4 text-blue-600 font-medium hover:text-blue-700 transition"
          >
            View Details →
          </Link>
        </div>
      </div>
    </section>
    

  </AppLayout>
</template>
