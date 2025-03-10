<script setup>
import { computed, ref } from 'vue';
import { CalendarIcon, CurrencyDollarIcon, MapPinIcon } from '@heroicons/vue/24/outline';
import { Link, useForm } from '@inertiajs/vue3';

import AppLayout from '@/Layout/AppLayout.vue';

const props = defineProps({
  job: {
    type: Object,
    required: true
  },
  isJobSeeker: {
    type: Boolean,
    default: false
  },
  applicationStatus: {
    type: String,

  },
  isOwner: {
    type: Boolean,
    default: false
  }
});

const showApplyForm = ref(false);
const handleApplyClick = () => {
  if (!showApplyForm.value) {
    showApplyForm.value = true; // Open form when clicking "Apply Now"
  } else {
    showApplyForm.value = false; // Close form when clicking "Cancel"
  }
};
const submitApplication = () => {
  form.post(`/apply/${props.job.id}`
    , {
    preserveScroll: true,
    onSuccess: () => {
      showApplyForm.value = false; 
      form.reset();
    }
  });
};


const form = useForm({
  resume_text: '',
  cover_letter: ''
});

const handleBack = () => {
  window.history.back();
};

const formattedSalary = computed(() => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(props.job.salary);
});
</script>

<template>
  <AppLayout>
    <div class="w-full flex justify-between items-center mt-8 px-6">
      <button @click="handleBack"
        class="px-5 py-2 text-sm font-medium border rounded-lg shadow-md transition-all
               bg-white text-gray-700 hover:bg-gray-100 border-gray-300">
        ← Go Back
      </button>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-10">
      <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-5">
          <h1 class="text-2xl font-bold text-gray-900">{{ job.title }}</h1>
          <p class="text-sm text-gray-600 mt-1">{{ job.company }}</p>
        </div>

        <div class="border-t border-gray-200 px-6 py-5">
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-gray-700">Description</dt>
              <dd class="mt-1 text-sm text-gray-600">{{ job.description }}</dd>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <CurrencyDollarIcon class="h-5 w-5 text-gray-400" />
              <span>{{ formattedSalary }} per year</span>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <dt>
                Tags :- 
              </dt>
              <dd>
                <ul v-if="job.tags.length>0" class="flex flex-wrap gap-2">
                  <li v-for="tag in job.tags" :key="tag.id"
                    class="px-2 py-1 bg-blue-100 text-blue-600 text-xs font-medium rounded-full">
                    {{ tag.name }}
                  </li>
                </ul>
                <div v-else>
                  No tags found

                </div>
              </dd>
            
             
            </div>
          </dl>
        </div>
      </div>

      <div class="mt-6 flex justify-end space-x-4">
        <button 
          v-if="isJobSeeker"
          :disabled="applicationStatus === 'approved' || applicationStatus === 'pending'"
          class="px-6 py-3 text-sm font-medium border rounded-lg shadow-md transition-all text-white"
          :class="{
            'bg-gray-400 cursor-not-allowed': applicationStatus === 'pending' || applicationStatus === 'approved',
            'bg-green-600 hover:bg-green-700': applicationStatus === 'approved',
            'bg-red-600 hover:bg-red-700': applicationStatus === 'rejected',
            'bg-blue-600 hover:bg-blue-700': !applicationStatus || applicationStatus === 'rejected'
          }"
          @click="handleApplyClick"
        >
          {{
            applicationStatus === 'pending' ? 'Pending' 
            : applicationStatus === 'approved' ? 'Approved' 
            : applicationStatus === 'rejected' ? 'Rejected - Apply Again' 
            : (showApplyForm ? 'Cancel' : 'Apply Now') 
          }}
        </button>
      
        <Link 
          v-if="isOwner"
          :href="`/Jobs/job/${job.id}/edit`"
          class="px-6 py-3 text-sm font-medium border rounded-lg shadow-md transition-all
                 bg-black text-white hover:bg-gray-900">
          Edit Job
        </Link>
      </div>
      
      <!-- Application Form -->
      <div v-if="showApplyForm" class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Apply for this position</h2>
        <form @submit.prevent="submitApplication">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Resume Text</label>
            <textarea
              v-model="form.resume_text"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': form.errors.resume_text }"
              rows="4"
              placeholder="Enter your resume text here..."
              required
            ></textarea>
            <p v-if="form.errors.resume_text" class="mt-1 text-sm text-red-500">
              {{ form.errors.resume_text }}
            </p>
          </div>
      
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cover Letter</label>
            <textarea
              v-model="form.cover_letter"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': form.errors.cover_letter }"
              rows="6"
              placeholder="Write your cover letter..."
              required
            ></textarea>
            <p v-if="form.errors.cover_letter" class="mt-1 text-sm text-red-500">
              {{ form.errors.cover_letter }}
            </p>
          </div>
      
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showApplyForm = false"
              class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Submitting...' : 'Submit Application' }}
            </button>
          </div>
        </form>
      </div>
      
    </div>
  </AppLayout>
</template>
