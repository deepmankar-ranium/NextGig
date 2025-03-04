<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Title -->
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl">Tags</h1>
                    <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
                        Browse all available tags for job listings
                    </p>
                </div>

                <!-- Create & Delete Buttons -->
                <div class="mt-10 flex items-center justify-between">
                    <button @click="showPopup = true"
                        class="px-4 py-2 border border-gray-500 text-gray-800 hover:bg-gray-900 hover:text-white transition">
                        Create a tag
                    </button>
                    <button @click="confirmDelete"
                        class="px-4 py-2 border border-red-500 text-red-600 hover:bg-red-600 hover:text-white transition">
                        Delete selected tags
                    </button>
                </div>

                <!-- Create Tag Modal -->
                <div v-if="showPopup"
                    class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-75 z-50">
                    <div class="bg-white rounded-lg shadow-xl sm:w-full sm:max-w-lg p-6 transition-all">
                        <h3 class="text-lg font-semibold text-gray-900">Create a new tag</h3>
                        <input type="text" v-model="tagName"
                            class="block w-full mt-3 rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-600 sm:text-sm"
                            placeholder="Enter tag name">

                        <!-- Buttons -->
                        <div class="mt-4 flex justify-end space-x-2">
                            <button @click="createTag"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">
                                Create
                            </button>
                            <button @click="closePopup"
                                class="px-4 py-2 bg-gray-200 text-gray-900 rounded hover:bg-gray-300">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div v-if="showDeleteConfirm" class="fixed inset-0 flex items-center justify-center bg-gray-500 bg-opacity-75 z-50">
                    <div class="bg-white rounded-lg shadow-xl sm:w-full sm:max-w-md p-6 transition-all">
                        <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                        <p class="mt-2 text-gray-600">Are you sure you want to delete the selected tags?</p>

                        <!-- Buttons -->
                        <div class="mt-4 flex justify-end space-x-2">
                            <button @click="deleteTags"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-500">
                                Yes, Delete
                            </button>
                            <button @click="showDeleteConfirm = false"
                                class="px-4 py-2 bg-gray-200 text-gray-900 rounded hover:bg-gray-300">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tag List -->
                <div class="mt-10">
                    <ul class="flex flex-col gap-2">
                        <li v-for="tag in props.tags" :key="tag.id"
                            class="col-span-1 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-full flex items-center justify-between p-6">
                                <div class="flex items-center space-x-3">
                                    <!-- Checkbox -->
                                    <input type="checkbox" :id="'tag-' + tag.id" v-model="selectedTags" :value="tag.id"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                                    <!-- Tag Name -->
                                    <label :for="'tag-' + tag.id" class="text-gray-900 text-sm font-medium truncate">
                                        {{ tag.name }}
                                    </label>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    tags: Array,
});

const showPopup = ref(false);
const showDeleteConfirm = ref(false);
const tagName = ref('');
const selectedTags = ref([]);

// Function to close modal and reset input
const closePopup = () => {
    tagName.value = '';
    showPopup.value = false;
};

// Function to open delete confirmation modal
const confirmDelete = () => {
    if (selectedTags.value.length === 0) {
        alert("Please select at least one tag to delete.");
        return;
    }
    showDeleteConfirm.value = true;
};

// Function to create tag using Inertia.js
const createTag = () => {
    if (!tagName.value.trim()) return; // Prevent empty submissions

    router.post('/create-tags', { name: tagName.value }, {
        onSuccess: () => {
            closePopup();
        },
        onError: (errors) => {
            console.log(errors); // Handle validation errors
        }
    });
};

// Function to delete selected tags with confirmation
const deleteTags = () => {
    router.post('/delete-tags', { tags: selectedTags.value }, {
        onSuccess: () => {
            selectedTags.value = [];
            showDeleteConfirm.value = false;
        },
        onError: (errors) => {
            console.log(errors);
        }
    });
};
</script>
