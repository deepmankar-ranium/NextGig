<script setup>
import { computed, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layout/AppLayout.vue';
import { ChevronLeftIcon } from 'lucide-vue-next';

// Define Props
const props = defineProps({
  employer: {
    type: Object,
    required: true
  },
  tags: {
    type: Array,
    required: true
  }
});

const handleBack = () => {
  window.history.back();
};

// Compute Employer Name Safely
const employerName = computed(() => props.employer?.name || '');

// Ensure Tags List is Always an Array
const tagsList = computed(() => props.tags || []);

// Storage key for form persistence
const STORAGE_KEY = 'job_form_data';

// Load saved form data if available
const loadFormFromStorage = () => {
  try {
    const savedData = localStorage.getItem(STORAGE_KEY);
    if (savedData) {
      const parsedData = JSON.parse(savedData);
      
      // Only restore the form if the employer ID matches
      if (parsedData && parsedData.employer_id === props.employer?.id) {
        return {
          title: parsedData.title || '',
          description: parsedData.description || '',
          employer_id: props.employer?.id,
          employer_name: employerName.value,
          salary: parsedData.salary || '',
          tags: parsedData.tags || [],
        };
      }
    }
  } catch (error) {
    console.error('Error loading saved form data:', error);
  }
  
  // Return default form data if no saved data or error
  return {
    title: '',
    description: '',
    employer_id: props.employer?.id,
    employer_name: employerName.value,
    salary: '',
    tags: [],
  };
};

// Define Form with initially loaded data
const form = useForm(loadFormFromStorage());

// Save form data to storage
const saveFormToStorage = () => {
  try {
    const dataToSave = {
      title: form.title,
      description: form.description,
      employer_id: form.employer_id,
      salary: form.salary,
      tags: form.tags,
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSave));
  } catch (error) {
    console.error('Error saving form data:', error);
  }
};

// Clear form and storage
const clearForm = () => {
  form.reset();
  localStorage.removeItem(STORAGE_KEY);
};

// Watch for changes in form data
watch(() => [form.title, form.description, form.salary], saveFormToStorage, { deep: true });

// Form Submission
const submit = () => {
  form.post(route('jobs.store'), { 
    preserveScroll: true,
    onSuccess: () => {
      // Clear storage after successful submission
      localStorage.removeItem(STORAGE_KEY);
    }
  });
};

// Check if we need to reconcile tags after component mounts
onMounted(() => {
  // Make sure we only keep tags that still exist in the current tagsList
  if (form.tags.length > 0) {
    const validTagIds = tagsList.value.map(tag => tag.id);
    form.tags = form.tags.filter(tagId => validTagIds.includes(tagId));
    saveFormToStorage();
  }
});
</script>

<template>
  <AppLayout>
    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Page Header -->
        <div class="pb-8 border-b border-gray-200">
          <button @click="handleBack" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
            <ChevronLeftIcon class="h-5 w-5 mr-1" />
            Back
          </button>
          <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Create a New Job Posting
          </h1>
          <p class="mt-2 text-lg text-gray-600">
            Fill out the details below to find your next great hire.
          </p>
        </div>

        <!-- Form Card -->
        <div class="mt-8">
          <form @submit.prevent="submit" class="space-y-8">
            <div class="bg-white p-8 rounded-xl shadow-md">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <label for="title" class="block text-sm font-medium text-gray-700">Job Title</label>
                        <input 
                            type="text" v-model="form.title" id="title"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                        <p v-if="form.errors?.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="employer_name" class="block text-sm font-medium text-gray-700">Employer</label>
                        <input 
                            type="text" v-model="form.employer_name" id="employer_name"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm bg-gray-100 cursor-not-allowed"
                            readonly 
                        />
                    </div>

                    <div class="sm:col-span-3">
                        <label for="salary" class="block text-sm font-medium text-gray-700">Salary (per year)</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input 
                                type="number" v-model="form.salary" id="salary"
                                class="block w-full pl-7 pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="0.00"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">USD</span>
                            </div>
                        </div>
                        <p v-if="form.errors?.salary" class="text-red-500 text-sm mt-1">{{ form.errors.salary }}</p>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">Job Description</label>
                        <textarea 
                            v-model="form.description" id="description" rows="6"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </textarea>
                        <p v-if="form.errors?.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-xl shadow-md">
                <h3 class="text-lg font-medium text-gray-900">Job Tags</h3>
                <p class="mt-1 text-sm text-gray-500">Select tags that best describe this role. You can manage your tags in the <Link href="/tags" class="text-indigo-600 hover:text-indigo-500 underline">Tag Manager</Link>.</p>
                
                <div v-if="tagsList.length > 0" class="mt-4 flex flex-wrap gap-3">
                    <div v-for="tag in tagsList" :key="tag.id">
                        <input 
                          type="checkbox"
                          :id="'tag-' + tag.id"
                          :value="tag.id"
                          v-model="form.tags"
                          class="hidden"
                          @change="saveFormToStorage"
                        />
                        <label 
                          :for="'tag-' + tag.id" 
                          class="cursor-pointer px-3 py-1.5 text-sm rounded-full border transition-colors"
                          :class="form.tags.includes(tag.id) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        >
                          {{ tag.name }}
                        </label>
                    </div>
                </div>
                <div v-else class="mt-4 text-center py-6 text-gray-500 bg-gray-50 rounded-lg border border-dashed">
                  <p>No tags available.</p>
                  <p class="text-sm">Visit the <Link href="/tags" class="text-indigo-600 hover:text-indigo-500 font-medium">Tag Manager</Link> to create some.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-4">
              <button 
                type="button" 
                @click="clearForm"
                class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg shadow-sm hover:bg-gray-50 text-sm font-medium">
                Clear Form
              </button>
              <button 
                type="submit" 
                class="inline-flex justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                :disabled="form.processing">
                {{ form.processing ? 'Creating...' : 'Create Job Listing' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>