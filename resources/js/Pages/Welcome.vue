<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import JobCard from '@/Components/JobCard.vue';
import { SearchIcon, XIcon } from 'lucide-vue-next';

const props = defineProps({
  jobListings: {
    type: Object,
    required: true,
  },
});

const searchQuery = ref('');
const isSearching = ref(false);
const searchResults = ref([]);
const hasTyped = ref(false);

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
    hasTyped.value = false;
};

let debounceTimer;
watch(searchQuery, (newValue) => {
  hasTyped.value = true;
  clearTimeout(debounceTimer);
  if (newValue.trim()) {
    isSearching.value = true;
    debounceTimer = setTimeout(async () => {
      try {
        const response = await axios.get('/search', { params: { q: newValue } });
        searchResults.value = response.data?.searchResults?.data || [];
      } catch (error) {
        console.error('Search request failed:', error);
        searchResults.value = [];
      } finally {
        isSearching.value = false;
      }
    }, 300);
  } else {
    searchResults.value = [];
    isSearching.value = false;
  }
});

const viewAllJobs = () => {
    router.get('/Jobs');
}
</script>

<template>
  <AppLayout>
    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header and Search Section -->
        <div class="text-center mb-12">
          <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gray-900">
            Find Your Next <span class="text-indigo-600">Opportunity</span>
          </h1>
          <p class="mt-4 max-w-2xl mx-auto text-lg md:text-xl text-gray-600">
            Your central hub for discovering job listings tailored to you.
          </p>

          <div class="mt-8 max-w-2xl mx-auto relative">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <SearchIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="searchQuery"
                type="search"
                placeholder="Search by title, company, or keyword..."
                class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-full placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                 <button @click="clearSearch" type="button" class="text-gray-400 hover:text-gray-600">
                    <XIcon class="h-5 w-5" />
                 </button>
              </div>
            </div>

            <!-- Search Results Dropdown -->
            <div v-if="searchQuery" class="absolute w-full bg-white shadow-lg rounded-lg mt-2 z-10 text-left">
              <div v-if="isSearching" class="p-4 text-gray-500">Searching...</div>
              <div v-else-if="searchResults.length > 0">
                <ul class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                  <li v-for="job in searchResults" :key="job.id">
                    <Link :href="`/Jobs/job/${job.id}`" class="block p-4 hover:bg-gray-50">
                      <h3 class="font-semibold text-gray-900">{{ job.title }}</h3>
                      <p class="text-sm text-gray-600">{{ job.description.substring(0, 70) }}...</p>
                    </Link>
                  </li>
                </ul>
              </div>
              <div v-else-if="!isSearching && hasTyped" class="p-4 text-gray-500">No jobs found for "{{ searchQuery }}".</div>
            </div>
          </div>
        </div>

        <!-- Featured Jobs Section -->
        <div>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900">Latest Job Postings</h2>
            <button @click="viewAllJobs" class="text-indigo-600 hover:text-indigo-800 font-medium">
              View All →
            </button>
          </div>

          <div v-if="jobListings.data && jobListings.data.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Link
              v-for="job in jobListings.data"
              :key="job.id"
              :href="`/Jobs/job/${job.id}`"
              class="block"
            >
              <JobCard :job="job" />
            </Link>
          </div>
          <div v-else class="text-center py-10 text-gray-500">
            No featured jobs available at the moment.
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
