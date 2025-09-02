<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layout/AppLayout.vue';

// ✅ Import only icons that exist in lucide-vue-next
import {
  CalendarIcon,
  MapPinIcon,
  ChevronLeftIcon,
  PlusIcon,
  Trash2,
  Building2,       // instead of <Building>
  DollarSignIcon,  // instead of <CurrencyDollarIcon>
  X                // close button
} from 'lucide-vue-next';

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
  showApplyForm.value = true;
};

const form = useForm({
  resume_text: '',
  cover_letter: ''
});

const submitApplication = () => {
  form.post(`/apply/${props.job.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showApplyForm.value = false;
      form.reset();
    }
  });
};

const handleBack = () => {
  window.history.back();
};

const formattedSalary = computed(() => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(props.job.salary);
});
</script>

<template>
  <AppLayout>
    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="pb-8 border-b border-gray-200">
            <button @click="handleBack" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ChevronLeftIcon class="h-5 w-5 mr-1" />
                Back
            </button>
            <div class="lg:flex lg:items-center lg:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">{{ job.title }}</h1>
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <!-- FIXED Building icon -->
                        <Building2 class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
                        <span>{{ job.employer.name }}</span>
                        <MapPinIcon class="flex-shrink-0 ml-4 mr-1.5 h-5 w-5 text-gray-400" />
                        <span>{{ job.location }}</span>
                    </div>
                </div>
                <div class="mt-5 flex lg:mt-0 lg:ml-4 space-x-3">
                    <button
                      v-if="isJobSeeker"
                      :disabled="applicationStatus === 'approved' || applicationStatus === 'pending'"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white"
                      :class="{
                        'bg-gray-400 cursor-not-allowed': applicationStatus === 'pending' || applicationStatus === 'approved',
                        'bg-green-600': applicationStatus === 'approved',
                        'bg-yellow-500': applicationStatus === 'pending',
                        'bg-indigo-600 hover:bg-indigo-700': !applicationStatus || applicationStatus === 'rejected'
                      }"
                      @click="handleApplyClick"
                    >
                      {{
                        applicationStatus === 'pending' ? 'Application Pending'
                        : applicationStatus === 'approved' ? 'Approved'
                        : applicationStatus === 'rejected' ? 'Apply Again'
                        : 'Apply Now'
                      }}
                    </button>

                    <Link
                      v-if="isOwner"
                      :href="`/Jobs/job/${job.id}/edit`"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50"
                    >
                      Edit Job
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mt-8 lg:grid lg:grid-cols-3 lg:gap-8">
            <!-- Left Column (Job Description) -->
            <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-md">
                <h2 class="text-xl font-bold text-gray-900">Job Description</h2>
                <div class="mt-4 prose prose-indigo text-gray-600 max-w-none">
                    <p>{{ job.description }}</p>
                </div>
            </div>

            <!-- Right Column (Summary) -->
            <div class="mt-8 lg:mt-0">
                <div class="bg-white p-6 rounded-xl shadow-md space-y-5">
                    <h3 class="text-lg font-bold text-gray-900">Job Overview</h3>
                    <div class="flex items-center">
                        <!-- FIXED Dollar icon -->
                        <DollarSignIcon class="h-5 w-5 text-gray-400 mr-3" />
                        <div>
                            <p class="text-sm text-gray-500">Salary</p>
                            <p class="font-semibold text-gray-800">{{ formattedSalary }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <CalendarIcon class="h-5 w-5 text-gray-400 mr-3" />
                        <div>
                            <p class="text-sm text-gray-500">Date Posted</p>
                            <p class="font-semibold text-gray-800">{{ new Date(job.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                    <div v-if="job.tags.length > 0">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Tags</h4>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="tag in job.tags" :key="tag.id" class="px-2 py-1 bg-indigo-50 text-indigo-700 text-xs font-medium rounded-full">
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>

    <!-- Application Form Slide-over -->
    <div v-if="showApplyForm" class="fixed inset-0 overflow-hidden z-40" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showApplyForm = false"></div>
        <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
          <div class="w-screen max-w-md">
            <div class="h-full flex flex-col bg-white shadow-xl overflow-y-scroll">
              <div class="p-6">
                <div class="flex items-start justify-between">
                  <h2 class="text-lg font-medium text-gray-900" id="slide-over-title">Apply for: {{ job.title }}</h2>
                  <div class="ml-3 h-7 flex items-center">
                    <button @click="showApplyForm = false" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                      <span class="sr-only">Close panel</span>
                      <!-- FIXED X icon -->
                      <X class="h-6 w-6" />
                    </button>
                  </div>
                </div>
              </div>
              <div class="border-t border-gray-200 px-6 py-4">
                <form @submit.prevent="submitApplication" class="space-y-6">
                  <div>
                    <label for="resume_text" class="block text-sm font-medium text-gray-700">Resume</label>
                    <div class="mt-1">
                      <textarea v-model="form.resume_text" id="resume_text" rows="8" class="block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md" placeholder="Paste your resume here..."></textarea>
                    </div>
                     <p v-if="form.errors.resume_text" class="mt-1 text-sm text-red-500">{{ form.errors.resume_text }}</p>
                  </div>
                  <div>
                    <label for="cover_letter" class="block text-sm font-medium text-gray-700">Cover Letter</label>
                    <div class="mt-1">
                      <textarea v-model="form.cover_letter" id="cover_letter" rows="8" class="block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md" placeholder="Why are you a great fit for this role?"></textarea>
                    </div>
                    <p v-if="form.errors.cover_letter" class="mt-1 text-sm text-red-500">{{ form.errors.cover_letter }}</p>
                  </div>
                  <div class="flex justify-end space-x-3">
                    <button @click="showApplyForm = false" type="button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                      {{ form.processing ? 'Submitting...' : 'Submit Application' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
