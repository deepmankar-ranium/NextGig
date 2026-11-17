<script setup>
import { computed, watch, onMounted, ref } from 'vue';
import axios from 'axios';
import { Sparkles } from 'lucide-vue-next';
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
          min_years_experience: parsedData.min_years_experience ?? '',
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
    min_years_experience: '',
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
      min_years_experience: form.min_years_experience,
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

// AI draft: fills the form only. The employer reviews and publishes it themselves.
const draftNotes = ref('');
const drafting = ref(false);
const draftError = ref('');
const draftNewSkills = ref([]);

const draftWithAi = async () => {
  if (!form.title.trim() || !draftNotes.value.trim()) {
    draftError.value = !form.title.trim() ? 'Add the job title first.' : 'Add a few notes about the role.';
    return;
  }
  drafting.value = true;
  draftError.value = '';
  try {
    const { data } = await axios.post('/jobs/draft', {
      title: form.title,
      notes: draftNotes.value,
      min_years_experience: form.min_years_experience || null,
    });
    form.description = data.draft.description;
    form.tags = [...new Set([...form.tags, ...data.draft.tag_ids])];
    draftNewSkills.value = data.draft.new_skills;
  } catch (e) {
    draftError.value = e.response?.data?.message || 'Could not draft right now. Please try again.';
  } finally {
    drafting.value = false;
  }
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
    <div>
      <div class="page-narrow max-w-4xl">

        <!-- Page Header -->
        <div class="pb-6 border-b border-gray-200">
          <Link href="/posted-jobs" class="mb-3 inline-flex items-center text-sm text-gray-500 hover:text-gray-800">
            <ChevronLeftIcon class="h-4 w-4 mr-1" />
            My postings
          </Link>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
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
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input 
                                type="number" v-model="form.salary" id="salary"
                                class="block w-full pl-7 pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="e.g. 1200000"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">INR</span>
                            </div>
                        </div>
                        <p v-if="form.errors?.salary" class="text-red-500 text-sm mt-1">{{ form.errors.salary }}</p>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="min_years_experience" class="block text-sm font-medium text-gray-700">Minimum years of experience (optional)</label>
                        <input type="number" min="0" max="40" v-model="form.min_years_experience" id="min_years_experience"
                            class="mt-1 block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="e.g. 3" />
                        <p class="text-xs text-gray-500 mt-1">Used with the job's tags to compute candidates' match scores.</p>
                        <p v-if="form.errors?.min_years_experience" class="text-red-500 text-sm mt-1">{{ form.errors.min_years_experience }}</p>
                    </div>

                    <!-- AI draft: sits right above the description it writes, and uses the title and years above. -->
                    <div class="sm:col-span-6 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                        <h2 class="flex items-center gap-2 text-sm font-semibold text-indigo-900"><Sparkles class="h-4 w-4" /> Draft the description with AI (optional)</h2>
                        <p class="mt-1 text-xs text-indigo-800">
                            Uses the job title{{ form.title.trim() ? ` "${form.title.trim()}"` : '' }} and minimum years above, plus your notes. It fills the description and suggests tags; nothing is published until you click Create.
                        </p>
                        <textarea v-model="draftNotes" rows="3" class="mt-3 w-full rounded-md border-indigo-200 text-sm" placeholder="e.g. Laravel + Vue, build internal tools, mentor juniors, remote-friendly team"></textarea>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <button type="button" @click="draftWithAi" :disabled="drafting" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                                {{ drafting ? 'Drafting…' : (form.description.trim() ? 'Redraft description' : 'Draft description') }}
                            </button>
                            <span v-if="draftError" class="text-sm text-red-600">{{ draftError }}</span>
                        </div>
                        <p v-if="draftNewSkills.length" class="mt-2 text-xs text-indigo-800">
                            Suggested skills without a tag yet: <strong>{{ draftNewSkills.join(', ') }}</strong>. Create them in the <Link href="/tags" class="underline">Tag Manager</Link> if you want them used for matching.
                        </p>
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