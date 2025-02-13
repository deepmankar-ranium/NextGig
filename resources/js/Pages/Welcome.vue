<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { CurrencyDollarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
  jobListings: {
    type: Object,
    required: true,
  },
  query: {
    type: String,
    default: '',
  },
});

const searchQuery = ref(props.query || '');
const isSearching = ref(false);
const hasSearched = ref(false);
const searchResults = ref([]); 

const submitSearch = async (e) => {
  e.preventDefault();
  if (!searchQuery.value.trim()) return;

  isSearching.value = true;
  hasSearched.value = true;

  try {
    const response = await axios.get('/search', {
      params: { q: searchQuery.value },
     
    });

    searchResults.value = response?.data?.searchResults?.data || [];
  } catch (error) {
    console.error('Search request failed:', error);
    searchResults.value = []; 
  } finally {
    isSearching.value = false;
  }
};


watch(searchQuery, (newValue) => {
  if (!newValue.trim()) {
    hasSearched.value = false;
    searchResults.value = [];
  }
});
</script>

<template>
  <AppLayout>
    <section class="relative bg-gray-900 text-white text-center py-20 px-6">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-5xl font-extrabold leading-tight">Find Your Dream Job Today</h1>
        <p class="mt-4 text-lg opacity-90">
          Explore thousands of job listings and connect with top employers.
        </p>

     
<div class="mt-8 relative max-w-2xl mx-auto">
  <form @submit.prevent="submitSearch" class="mb-6">
    <div class="relative">
      <input 
        v-model="searchQuery" 
        type="text" 
        placeholder="Search jobs..." 
        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-4 py-3 pr-12 text-gray-900 bg-white dark:bg-gray-800 dark:text-white dark:border-gray-700"
      />
      <button 
        type="submit" 
        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200"
        :disabled="isSearching"
      >
        <svg v-if="!isSearching" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <svg v-else class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </button>
    </div>
  </form>

<div v-if="hasSearched" class="absolute w-full bg-white shadow-lg rounded-lg -mt-6 z-10">
  <div v-if="searchResults.length > 0">
    <ul class="divide-y divide-gray-200">
      <li v-for="job in searchResults" :key="job.id">
        <Link :href="`/Jobs/job/${job.id}`" class="p-4">
          <div class="flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900">{{ job.title }}</h3>
            <p class="text-green-600 font-semibold">
              {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(job.salary) }} per year
            </p>
          </div>
          <p class="text-gray-600 line-clamp-2">
            {{ job.description.length > 120 ? job.description.substring(0, 120) + '...' : job.description }}
          </p>
        </Link>
      </li>
    </ul>
  </div>

  <p v-else class="text-lg text-gray-500">No jobs found matching your search.</p>
</div>
</div>
        <div class="mt-6 flex justify-center space-x-4">
          <Link href="/Jobs" class="bg-white text-gray-900 px-6 py-3 rounded-lg shadow-md font-medium hover:bg-gray-200 transition duration-200">
            Browse Jobs
          </Link>
          <Link href="/contact" class="border border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-900 transition duration-200">
            Contact Us
          </Link>
        </div>
      </div>
    </section>


    <section class="max-w-7xl mx-auto py-16 px-6 space-y-16">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900">Why Choose Us?</h2>
        <p class="mt-3 text-gray-600 text-2xl">We help job seekers and employers connect efficiently.</p>
      </div>

      <div>
        <h2 class="text-4xl font-bold text-gray-900 mb-6 text-center">Featured Jobs</h2>
        
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div 
            v-for="job in jobListings.data" 
            :key="job.id"
            class="bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition-shadow duration-300 ease-in-out"
          >
            <h3 class="text-lg font-semibold text-gray-900">{{ job.title }}</h3>
            <p class="text-green-600 font-semibold mt-1">
              {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(job.salary) }} per year
            </p>
            <p class="mt-3 text-gray-600 line-clamp-3">
              {{ job.description.length > 120 ? job.description.substring(0, 120) + '...' : job.description }}
            </p>
            <p class="mt-2 text-gray-500">
              Tags: <span v-for="(tag, index) in job.tags" :key="tag.id">{{ tag.name }}<span v-if="index < job.tags.length - 1">, </span></span>
            </p>
            <Link :href="`/Jobs/job/${job.id}`" class="inline-block mt-4 text-blue-600 font-medium hover:text-blue-700 transition">
              View Details →
            </Link>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
