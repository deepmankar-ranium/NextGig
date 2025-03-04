<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto p-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Add Job</h1>

      <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="form-group">
              <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                Job Title:
              </label>
              <input type="text" v-model="form.title" id="title"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" />
              <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
            </div>

            <div class="form-group">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                Job Description:
              </label>
              <textarea v-model="form.description" id="description" rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"></textarea>
              <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>

            <div class="form-group">
              <label for="employer_id" class="block text-sm font-medium text-gray-700 mb-1">
                Employer ID:
              </label>
              <input type="number" v-model="form.employer_id" id="employer_id" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" 
                readonly />
              <p v-if="form.errors.employer_id" class="text-red-500 text-sm mt-1">{{ form.errors.employer_id }}</p>
            </div>

            <div class="form-group">
              <label for="salary" class="block text-sm font-medium text-gray-700 mb-1">
                Salary:
              </label>
              <input type="number" v-model="form.salary" id="salary"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" />
              <p v-if="form.errors.salary" class="text-red-500 text-sm mt-1">{{ form.errors.salary }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Tags:</label>
                
                <div class="flex flex-wrap gap-4 mt-4">
                 

                    <div class="flex flex-wrap gap-3">
                        <div v-for="tag in props.tags" :key="tag.id" 
                            class="flex items-center px-4 py-2 bg-white rounded-full border border-gray-200 shadow-sm hover:border-blue-400 transition-colors">
                            <input 
                                type="checkbox"
                                :id="'tag-' + tag.id"
                                :value="tag.id"
                                v-model="form.tags"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <label :for="'tag-' + tag.id" class="ml-2 text-gray-700 cursor-pointer select-none">
                                {{ tag.name }}
                            </label>
                        </div>

                        <div v-if="props.tags.length === 0" 
                            class="w-full text-center py-4 text-gray-500 bg-white rounded-lg border border-gray-200">
                            <span class="block mb-1">No tags available</span>
                            <span class="text-sm">Create some tags in the Tag Manager</span>
                        </div>
                    </div>
                    <div class="w-full">
                      <p class="text-gray-600 mb-4">
                          Want to add custom tags? Visit the <Link href="/tags" class="text-blue-600 hover:text-blue-800 underline">Tag Manager</Link>
                      </p>
                  </div>
                </div>
            </div>

            <button type="submit" 
              class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition"
              :disabled="form.processing">
              {{ form.processing ? 'Creating...' : 'Create Job Listing' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

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

const form = useForm({
  title: '',
  description: '',
  employer_id: props.employer.id,
  salary: '',
  tags: [],
});

const submit = () => {
  form.post('/Jobs', {
    preserveScroll: true,
  });
};
</script>
