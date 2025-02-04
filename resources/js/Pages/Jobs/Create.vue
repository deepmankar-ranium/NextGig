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
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" />
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

            <button type="submit" 
            class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition">
              Create Job Listing
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

const form = useForm({
  title: '',
  description: '',
  employer_id: '',
  salary: '',
});

const submit = () => {
  form.post('/Jobs', {
    preserveScroll: true,
  });
};
</script>
