<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="w-full flex justify-between items-center mt-8 px-6">
      <button @click="handleBack"
        class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all bg-white text-gray-700 hover:bg-gray-100 border-gray-300"
      >
        ← Go Back
      </button>
      <Link 
        v-if="isEmployer" 
        href="/Jobs/create" 
        class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all bg-black text-white hover:bg-gray-900"
      >
        + Create Job
      </Link>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-10">

      <div class="flex flex-col space-y-2 py-5 justify-center">
        <h2 class="text-2xl font-bold text-gray-900">
          Search Jobs
        </h2>
   <form @submit.prevent="searchJobs" class="flex space-x-4 relative">
    <input v-model="searchQuery" type="text" name="search" placeholder="Search for jobs" class=" w-full px-4 py-2 border border-gray-200 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 transition-colors">Search</button>
    <button v-if="searchQuery" type="button" @click="removeSearchFilter" class="absolute right-28 top-1 text-2xl font-bold">×</button>
   </form>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Available Positions</h1>
      
      <!-- Tags Filter -->
       <h2 class="text-xl font-semibold text-gray-900 mb-4">
        Filter by Tags
       </h2>
      <div v-if="tags?.length" class="flex flex-wrap gap-2 mb-8">
        <button
          v-for="tag in tags"
          :key="tag.id"
          @click="filterJobs(tag.name)"
          :class="[
            'px-4 py-2 rounded transition-colors',
            selectedTag === tag.name ? 'bg-gray-900 text-white' : 'bg-gray-200 hover:bg-gray-300'
          ]"
          :disabled="isLoading"
        >
          {{ tag.name }}
        </button>
        <button 
          v-if="selectedTag"
          @click="filterJobs('')"
          :disabled="isLoading"
          class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
        >
          Clear Filter
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-10">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto"></div>
      </div>

      <!-- Job Listings Grid -->
      <div v-else-if="hasJobs" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Link 
          v-for="job in jobListingsData.data" 
          :key="job.id" 
          :href="`/Jobs/job/${job.id}`"
        >
        <!-- Higher order component -->
          <withRecentlyPostedBanner :job="job">
            <JobCard :job="job" />
          </withRecentlyPostedBanner>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-10 text-gray-600">
        No job listings found.
      </div>

      <!-- Pagination -->
      <Pagination 
        v-if="showPagination"
        :links="jobListingsData.links"
        @page-changed="handlePageChange"
        class="mt-10" 
      />
    </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { BriefcaseIcon, MapPinIcon, CurrencyDollarIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import Pagination from '../Components/Pagination.vue';
import axios from 'axios';
import { ref, computed, onMounted,watch } from 'vue';
import JobCard from '@/Components/JobCard.vue';
import withRecentlyPostedBanner from '@/Components/withRecentlyPostedBanner.vue';

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
const response = await axios.get('/search', {
  params: { q: searchQuery.value },
});

jobListingsData.value=response.data.searchResults;
console.log(jobListingsData.value);
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

// Composable functions

// API calls
const filterJobs = async (tag) => {
  try {

    selectedTag.value = tag;
    
    const response = await axios.get('/filter', { 
      params: { tag }
    });
    
    jobListingsData.value = response.data.jobListings;
    
    
  } catch (error) {
    console.error('Error filtering jobs:', error);
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