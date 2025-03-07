<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto p-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Add Job</h1>

      <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Job Title -->
            <div class="form-group">
              <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                Job Title:
              </label>
              <input 
                type="text" v-model="form.title" id="title"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" 
              />
              <p v-if="form.errors?.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <!-- Job Description -->
            <div class="form-group">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                Job Description:
              </label>
              <textarea 
                v-model="form.description" id="description" rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500">
              </textarea>
              <p v-if="form.errors?.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>

            <!-- Employer Name (Read-Only) -->
            <div class="form-group">
              <label for="employer_name" class="block text-sm font-medium text-gray-700 mb-1">
                Employer:
              </label>
              <input 
                type="text" v-model="form.employer_name" id="employer_name"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" 
                readonly 
              />
              <p v-if="form.errors?.employer_name" class="text-red-500 text-sm mt-1">{{ form.errors.employer_name }}</p>
            </div>

            <!-- Salary -->
            <div class="form-group">
              <label for="salary" class="block text-sm font-medium text-gray-700 mb-1">
                Salary:
              </label>
              <input 
                type="number" v-model="form.salary" id="salary"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" 
              />
              <p v-if="form.errors?.salary" class="text-red-500 text-sm mt-1">{{ form.errors.salary }}</p>
            </div>

            <!-- Tags Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Select Tags:</label>

              <div class="flex flex-wrap gap-4 mt-4">
                <div class="flex flex-wrap gap-3">
                  <div v-for="tag in tagsList" :key="tag.id" 
                    class="flex items-center px-4 py-2 bg-white rounded-full border border-gray-200 shadow-sm hover:border-blue-400 transition-colors">
                    <input 
                      type="checkbox"
                      :id="'tag-' + tag.id"
                      :value="tag.id"
                      v-model="form.tags"
                      class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                      @change="saveFormToStorage"
                    />
                    <label :for="'tag-' + tag.id" class="ml-2 text-gray-700 cursor-pointer select-none">
                      {{ tag.name }}
                    </label>
                  </div>
                </div>

                <!-- No Tags Available -->
                <div v-if="tagsList.length === 0" 
                  class="w-full text-center py-4 text-gray-500 bg-white rounded-lg border border-gray-200">
                  <span class="block mb-1">No tags available</span>
                  <span class="text-sm">Create some tags in the Tag Manager</span>
                </div>

                <!-- Link to Tag Manager -->
                <div class="w-full">
                  <p class="text-gray-600 mb-4">
                    Want to add custom tags? Visit the 
                    <Link href="/tags" class="text-blue-600 hover:text-blue-800 underline">
                      Tag Manager
                    </Link>
                  </p>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex space-x-4">
              <button 
                type="submit" 
                class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition"
                :disabled="form.processing">
                {{ form.processing ? 'Creating...' : 'Create Job Listing' }}
              </button>
              
              <button 
                type="button" 
                @click="clearForm"
                class="border border-gray-300 bg-white text-gray-700 px-6 py-3 rounded-lg shadow-md font-medium hover:bg-gray-50 transition">
                Clear Form
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, watch, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

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