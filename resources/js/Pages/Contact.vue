<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import PageContainer from '@/Components/PageContainer.vue';
</script>

<template>
  <AppLayout>
    <PageContainer>
      <div class="text-center">
        <h2 class="text-4xl font-bold text-gray-900">Contact Us</h2>
        <p class="mt-3 text-gray-500 text-xl">We'd love to hear from you</p>
      </div>

      <div class="mt-16 max-w-lg mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
          <div class="px-6 py-8">
            <form class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea rows="4" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
              </div>
              <button type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Send Message
              </button>
            </form>
          </div>
        </div>
      </div>
    </PageContainer>
  </AppLayout>
</template>
