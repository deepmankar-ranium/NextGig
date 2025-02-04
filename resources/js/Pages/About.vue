<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import PageContainer from '@/Components/PageContainer.vue';
</script>

<template>
  <AppLayout>
    <PageContainer>
      <div class="text-center">
        <h2 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
          About Us
        </h2>
        <p class="mt-3 max-w-md mx-auto text-gray-600 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
          Welcome to NextGig, where we connect talented professionals with their dream careers.
        </p>
      </div>

      <div class="mt-16 bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-8">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h3>
          <p class="text-gray-600">
            We're dedicated to revolutionizing the job search experience by creating meaningful connections 
            between employers and job seekers.
          </p>
        </div>
      </div>
    </PageContainer>
  </AppLayout>
</template>
