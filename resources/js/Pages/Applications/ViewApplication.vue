<template>
  <AppLayout>
    <div class=" p-10"> 
      <div class="flex justify-between items-center mb-6">
        <!-- Heading -->
        <h1 class="text-3xl font-extrabold text-gray-900">Applications</h1>
  
      </div>
      

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
                <button class="bg-gray-700 text-white px-4 py-2 rounded" @click="openPopup('resume', application.resume_text)">View Resume</button>
              </td>
              <td class="px-2 py-4">
                <button class="bg-gray-700 text-white px-4 py-2 rounded" @click="openPopup('coverLetter', application.cover_letter)">View Cover Letter</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Popup v-if="isPopupVisible" :content="popupContent" @close="closePopup" />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import { defineProps, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import Popup from '@/Components/Popup.vue';


const props = defineProps({
  applications: {
    type: Array,
    required: true
  }
});

const isPopupVisible = ref(false);
const popupContent = ref('');

const openPopup = (type, content) => {
  popupContent.value = content;
  isPopupVisible.value = true;
};

const closePopup = () => {
  isPopupVisible.value = false;
};
</script>