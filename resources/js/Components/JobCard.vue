<template>
  <div class="bg-white border border-gray-200 shadow-sm rounded-lg transition-all hover:shadow-md">
    <div class="p-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-900">{{ job.title }}</h2>
        <span v-if="job.match_score !== undefined && job.match_score !== null"
          class="ml-2 shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
          :class="job.match_score >= 75 ? 'bg-green-100 text-green-700' : job.match_score >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'"
          title="Your match score (AI suggestion, not a hiring decision)">
          {{ job.match_score }}% match
        </span>

      </div>

      <p class="text-gray-600 text-sm mb-4">{{ truncate(job.description) }}</p>

      <div class="space-y-2">
        <div v-if="job.location" class="flex items-center text-sm text-gray-600">
          <MapPinIcon class="h-5 w-5 mr-2 text-gray-400" />
          {{ job.location }}
        </div>
        <div class="flex items-center text-sm text-gray-600">
          <IndianRupeeIcon class="h-5 w-5 mr-2 text-gray-400" />
          {{ formatSalary(job.salary) }}
        </div>
        <div v-if="job.employer?.name" class="flex items-center text-sm text-gray-600">
          <BuildingOfficeIcon class="h-5 w-5 mr-2 text-gray-400" />
          {{ job.employer.name }}
        </div>
        <div v-if="job.created_at" class="flex items-center text-sm text-gray-600">
          <span class="font-medium">Posted on:</span>
          <span class="ml-1">{{ new Date(job.created_at).toLocaleDateString() }}</span>
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

  </div>
</template>
<script setup>
const props=defineProps({
  job:Object
})
import { BriefcaseIcon, MapPinIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import { IndianRupeeIcon } from 'lucide-vue-next';

const formatSalary = (salary) => {
  if (!salary) return 'Salary not specified';
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    maximumFractionDigits: 0
  }).format(salary);
};

const truncate = (text, length = 100) => {
  if (!text) return '';
  return text.length > length ? `${text.substring(0, length)}...` : text;
};



</script>
