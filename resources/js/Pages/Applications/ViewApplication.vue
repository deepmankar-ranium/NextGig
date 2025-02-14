<template>
  <AppLayout>
    <div class=" p-10">
      <h1 class="text-2xl font-bold mb-4">Applications</h1>
      <div class="overflow-x-auto w-full  flex justify-center">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
          <thead class="bg-gray-200 text-gray-600 uppercase text-sm">
            <tr>
              <th scope="col" class="px-2 py-3 text-left">Application ID</th>
              <th scope="col" class="px-2 py-3 text-left">Job Listing</th>
              <th scope="col" class="px-2 py-3 text-left">Job Seeker</th>
              <th scope="col" class="px-2 py-3 text-left">Resume</th>
              <th scope="col" class="px-2 py-3 text-left">Cover Letter</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="application in applications" :key="application.id" class="border-b border-gray-200 hover:bg-gray-100">
              <td class="px-2 py-4 font-medium">{{ application.id }}</td>
              <td class="px-2 py-4">{{ application.job_listing.title }}</td>
              <td class="px-2 py-4">{{ application.job_seeker.name }}</td>
              <td class="px-2 py-4">
                <div class="max-w-[350px] h-32 overflow-y-auto">{{ application.resume_text }}</div>
              </td>
              <td class="px-2 py-4">
                <div class="max-w-[350px] h-32 overflow-y-auto">{{ application.cover_letter }}</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
  applications: {
    type: Array,
    required: true
  }
});
</script>