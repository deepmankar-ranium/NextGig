<script setup>
import AppLayout from '@/Layout/AppLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ChevronLeftIcon, PlusIcon, Trash2, Tag as TagIcon } from 'lucide-vue-next';

const props = defineProps({
    tags: Array,
});

const showPopup = ref(false);
const showDeleteConfirm = ref(false);
const tagName = ref('');
const selectedTags = ref([]);

const handleBack = () => {
  window.history.back();
};

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
        preserveScroll: true,
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
        preserveScroll: true,
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

<template>
    <AppLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Page Header -->
                <div class="md:flex md:items-center md:justify-between pb-8 border-b border-gray-200">
                    <div class="min-w-0 flex-1">
                        <button @click="handleBack" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                            <ChevronLeftIcon class="h-5 w-5 mr-1" />
                            Back
                        </button>
                        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                            Manage Job Tags
                        </h1>
                        <p class="mt-2 text-lg text-gray-600">
                            Create, view, and delete tags to categorize your job listings.
                        </p>
                    </div>
                    <div class="mt-6 flex space-x-3 md:mt-0 md:ml-4">
                        <button @click="showPopup = true" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                            Create Tag
                        </button>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="mt-8 h-12">
                    <div v-if="selectedTags.length > 0" class="flex items-center justify-between bg-indigo-50 p-3 rounded-lg">
                        <p class="text-sm font-medium text-indigo-700">{{ selectedTags.length }} tag(s) selected</p>
                        <button @click="confirmDelete" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <Trash2 class="h-4 w-4 mr-1.5" />
                            Delete Selected
                        </button>
                    </div>
                </div>

                <!-- Tag List -->
                <div class="mt-4 bg-white rounded-xl shadow-md overflow-hidden">
                    <ul class="divide-y divide-gray-200">
                        <li v-for="tag in props.tags" :key="tag.id" class="hover:bg-gray-50 transition-colors">
                            <label :for="'tag-' + tag.id" class="w-full flex items-center justify-between p-4 cursor-pointer">
                                <div class="flex items-center space-x-4">
                                    <input type="checkbox" :id="'tag-' + tag.id" v-model="selectedTags" :value="tag.id"
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
                                    <span class="text-gray-900 text-sm font-medium truncate">
                                        {{ tag.name }}
                                    </span>
                                </div>
                                <TagIcon class="h-5 w-5 text-gray-400" />
                            </label>
                        </li>
                         <li v-if="props.tags.length === 0" class="text-center py-10 text-gray-500">
                            No tags found. Get started by creating a new one.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Create Tag Modal -->
        <div v-if="showPopup" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click="closePopup">
            <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full mx-auto overflow-hidden shadow-xl transform transition-all" @click.stop>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Create a new tag</h3>
                    <div class="mt-4">
                        <input type="text" v-model="tagName" @keydown.enter="createTag"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Enter tag name">
                    </div>
                    <div class="mt-6 flex justify-end gap-4">
                        <button @click="closePopup" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button @click="createTag" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg transition-colors text-sm font-medium">
                            Create
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click="showDeleteConfirm = false">
            <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
            <div class="relative bg-white rounded-lg max-w-sm w-full mx-auto overflow-hidden shadow-xl transform transition-all" @click.stop>
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <Trash2 class="h-6 w-6 text-red-600" />
                    </div>
                    <h3 class="text-lg font-medium mt-5">Confirm Deletion</h3>
                    <p class="text-gray-500 text-sm mt-2 mb-6">
                        Are you sure you want to delete the selected tag(s)? This action cannot be undone.
                    </p>
                    <div class="flex justify-center gap-4">
                        <button @click="showDeleteConfirm = false" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button @click="deleteTags" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-lg transition-colors text-sm font-medium">
                            Yes, Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
