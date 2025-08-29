<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import Pagination from '../Components/Pagination.vue';
import axios from 'axios';
import { ref, computed, onMounted, watch } from 'vue';
import JobCard from '@/Components/JobCard.vue';
import withRecentlyPostedBanner from '@/Components/withRecentlyPostedBanner.vue';
import { ChevronLeftIcon, PlusIcon, SearchIcon, XIcon } from 'lucide-vue-next';

// Props
const props = defineProps({
  jobListings: {
    type: Object,
    required: true
  },
  isEmployer: {
    type: Boolean,
    default: false
  },
  tags: {
    type: Array,
    default: () => []
  }
});
const searchQuery = ref('');
const searchJobs =async(e)=>{
  e.preventDefault();
  isLoading.value = true;
  try {
    const response = await axios.get('/search', {
      params: { q: searchQuery.value },
    });
    jobListingsData.value=response.data.searchResults;
    console.log(jobListingsData.value);
  } catch (error) {
      console.error('Search request failed:', error);
  } finally {
      isLoading.value = false;
  }
}

const removeSearchFilter = () => {
  searchQuery.value = '';
  jobListingsData.value = props.jobListings;
};


const handleBack = () => {
  window.history.back();
};



// Reactive state
const jobListingsData = ref(props.jobListings);
const isLoading = ref(false);
const selectedTag = ref('');

// API calls
const filterJobs = async (tag) => {
    isLoading.value = true;
  try {

    selectedTag.value = tag;

    const response = await axios.get('/filter', {
      params: { tag }
    });

    jobListingsData.value = response.data.jobListings;


  } catch (error) {
    console.error('Error filtering jobs:', error);
  } finally {
      isLoading.value = false;
  }
};

const handlePageChange = async (page) => {
  try {
    isLoading.value = true;
    const response = await axios.get('/filter', {
      params: {
        tag: selectedTag.value,
        page
      }
    });

    jobListingsData.value = response.data.jobListings;

    // Update URL
    const url = new URL(window.location);
    if (page > 1) {
      url.searchParams.set('page', page);
    } else {
      url.searchParams.delete('page');
    }
    window.history.pushState({}, '', url);

  } catch (error) {
    console.error('Error changing page:', error);
  } finally {
    isLoading.value = false;
  }
};

// Initialize with URL params
onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const tagFromUrl = urlParams.get('tag');
  if (tagFromUrl) {
    filterJobs(tagFromUrl);
  }
});

// Computed properties
const hasJobs = computed(() =>
  jobListingsData.value?.data?.length > 0
);

const showPagination = computed(() =>
  jobListingsData.value?.links?.length > 3
);
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
              Available Job Positions
            </h1>
            <p class="mt-2 text-lg text-gray-600">
              Find your next career move from our curated list of opportunities.
            </p>
          </div>
          <div v-if="isEmployer" class="mt-6 flex space-x-3 md:mt-0 md:ml-4">
            <Link
              href="/Jobs/create"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
              Post a New Job
            </Link>
          </div>
        </div>

        <!-- Filters Panel -->
        <div class="mt-8 p-6 bg-white rounded-xl shadow-md space-y-6">
            <!-- Search Form -->
            <form @submit.prevent="searchJobs">
                <label for="search" class="block text-sm font-medium text-gray-700">Search by keyword</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <div class="relative flex-grow focus-within:z-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <SearchIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input 
                            v-model="searchQuery" 
                            type="search" 
                            id="search"
                            placeholder="e.g., 'Software Engineer' or 'React'" 
                            class="block w-full pl-10 py-2 border-gray-300 rounded-none rounded-l-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                         <button v-if="searchQuery" @click="removeSearchFilter" type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <XIcon class="h-5 w-5" />
                        </button>
                    </div>
                    <button type="submit" class="relative -ml-px inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <span>Search</span>
                    </button>
                </div>
            </form>
            
            <!-- Tags Filter -->
            <div v-if="tags?.length" class="pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-700">Filter by Tags</h3>
                    <button v-if="selectedTag" @click="filterJobs('')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Clear Filter</button>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button
                        v-for="tag in tags"
                        :key="tag.id"
                        @click="filterJobs(tag.name)"
                        :disabled="isLoading"
                        :class="[
                            'px-3 py-1 text-sm rounded-full transition-colors',
                            selectedTag === tag.name ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                        ]"
                    >
                        {{ tag.name }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading or Content -->
        <div class="mt-8">
            <div v-if="isLoading" class="text-center py-16">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600">Loading...</p>
            </div>
            <div v-else>
                <div v-if="hasJobs" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                  <Link 
                    v-for="job in jobListingsData.data" 
                    :key="job.id" 
                    :href="`/Jobs/job/${job.id}`"
                    class="block"
                  >
                    <withRecentlyPostedBanner :job="job">
                      <JobCard :job="job" />
                    </withRecentlyPostedBanner>
                  </Link>
                </div>
                <div v-else class="text-center py-16">
                  <h3 class="text-lg font-medium text-gray-900">No job listings found</h3>
                  <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination 
          v-if="showPagination"
          :links="jobListingsData.links"
          @page-changed="handlePageChange"
          class="mt-10" 
        />
      </div>
    </div>
  </AppLayout>
</template>
