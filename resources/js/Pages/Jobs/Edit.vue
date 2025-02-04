<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import {Link} from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    job: Object,
    errors: Object,
})


// Add ref for controlling modal visibility
const showDeleteModal = ref(false);

const deleteJob = () => {
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    form.delete(`/Jobs/job/${props.job.id}`);
    showDeleteModal.value = false;
};

// Add ref for controlling edit modal visibility
const showEditModal = ref(false);

const form = useForm({
    title: props.job.title || '',
    description: props.job.description || '',
    salary: Math.round(props.job.salary) || 0,
    employer_id: props.job.employer_id || null,
})
const submit = () => {
    showEditModal.value = true;
};

const confirmEdit = () => {
    form.patch(`/Jobs/job/${props.job.id}/edit`);
    showEditModal.value = false;
};
</script>
<template>

    <AppLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-4xl font-bold mb-6 text-gray-800">Edit this job</h1>
            <form @submit.prevent="submit">
                <div class="mb-6">
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
                            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{
                                form.errors.description }}</p>
                        </div>
                        <div class="form-group">
                            <label for="employer_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Employer ID:
                            </label>
                            <input type="number" v-model="form.employer_id" id="employer_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" />
                            <p v-if="form.errors.employer_id" class="text-red-500 text-sm mt-1">{{
                                form.errors.employer_id }}</p>
                        </div>
                        <div class="form-group">
                            <label for="salary" class="block text-sm font-medium text-gray-700 mb-1">
                                Salary:
                            </label>
                            <input type="number" v-model="form.salary" id="salary"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500" />
                            <p v-if="form.errors.salary" class="text-red-500 text-sm mt-1">{{ form.errors.salary }}</p>
                        </div>
                        <div class="w-full flex justify-between">
                            <button type="button" @click="deleteJob"
                                class="border bg-red-500 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition"
                                :disabled="form.processing">
                                Delete job
                            </button>
                            <button type="submit"
                                class="border bg-gray-900 text-white border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-600 transition"
                                :disabled="form.processing">
                                {{ form.processing ? "Editing..." : "Confirm Edit" }}
                            </button>
                        </div>
                    </form>
                </div>
            </form>

            <!-- Add the edit confirmation modal -->
            <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                    <h2 class="text-xl font-bold mb-4">Confirm Edit</h2>
                    <p class="mb-6">Are you sure you want to edit this job? This action will update the job details.</p>
                    <div class="flex justify-end space-x-4">
                        <button @click="showEditModal = false" 
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Cancel
                        </button>
                        <button @click="confirmEdit" 
                            class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800">
                            Confirm Edit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add the delete confirmation modal -->
            <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                    <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
                    <p class="mb-6">Are you sure you want to delete this job? This action cannot be undone.</p>
                    <div class="flex justify-end space-x-4">
                        <button @click="showDeleteModal = false" 
                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                            Cancel
                        </button>
                        <button @click="confirmDelete" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>