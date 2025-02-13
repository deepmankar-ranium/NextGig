<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { BriefcaseIcon, MapPinIcon, CurrencyDollarIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import Pagination from '../Components/Pagination.vue';
import axios from 'axios';
import { ref, computed, onMounted } from 'vue';

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

// Reactive state
const jobListingsData = ref(props.jobListings);
const isLoading = ref(false);
const selectedTag = ref('');

// Composable functions
const formatSalary = (salary) => {
  if (!salary) return 'Salary not specified';
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD', 
    maximumFractionDigits: 0 
  }).format(salary);
};

const truncate = (text, length = 100) => {
  if (!text) return '';
  return text.length > length ? `${text.substring(0, length)}...` : text;
};

// API calls
const filterJobs = async (tag) => {
  try {
    isLoading.value = true;
    selectedTag.value = tag;
    
    const response = await axios.get('/filter', { 
      params: { tag }
    });
    
    jobListingsData.value = response.data.jobListings;
    
    // Update URL
    const url = new URL(window.location);
    if (tag) {
      url.searchParams.set('tag', tag);
    } else {
      url.searchParams.delete('tag');
    }
    window.history.pushState({}, '', url);
    
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
    <!-- Header Section -->
    <div class="w-full flex justify-between items-center mt-8 px-6">
      <Link 
        href="/home" 
        class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all bg-white text-gray-700 hover:bg-gray-100 border-gray-300"
      >
        ← Go Back
      </Link>
      <Link 
        v-if="isEmployer" 
        href="/jobs/create" 
        class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all bg-black text-white hover:bg-gray-900"
      >
        + Create Job
      </Link>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Available Positions</h1>
      
      <!-- Tags Filter -->
      <div v-if="tags?.length" class="flex flex-wrap gap-2 mb-4">
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
          class="bg-white border border-gray-200 shadow-sm rounded-lg transition-all hover:shadow-md"
        >
          <div class="p-6">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-lg font-semibold text-gray-900">{{ job.title }}</h2>
              <span 
                v-if="job.isNew" 
                class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full"
              >
                New
              </span>
            </div>
            
            <p class="text-gray-600 text-sm mb-4">{{ truncate(job.description) }}</p>

            <div class="space-y-2">
              <div v-if="job.location" class="flex items-center text-sm text-gray-600">
                <MapPinIcon class="h-5 w-5 mr-2 text-gray-400" />
                {{ job.location }}
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <CurrencyDollarIcon class="h-5 w-5 mr-2 text-gray-400" />
                {{ formatSalary(job.salary) }}
              </div>
              <div v-if="job.employer?.name" class="flex items-center text-sm text-gray-600">
                <BuildingOfficeIcon class="h-5 w-5 mr-2 text-gray-400" />
                {{ job.employer.name }}
              </div>
              <div v-if="job.tags?.length" class="flex items-center text-sm text-gray-600">
                <span class="font-medium">Tags:</span>
                <span v-for="(tag, index) in job.tags" :key="tag.id" class="ml-1">
                  {{ tag.name }}<span v-if="index < job.tags.length - 1">, </span>
                </span>
              </div>
            </div>
          </div>

          <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <span class="text-blue-600 font-medium hover:text-gray-900 transition">View Details →</span>
          </div>
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