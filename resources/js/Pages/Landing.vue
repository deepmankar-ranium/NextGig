<script setup>
import { Link } from '@inertiajs/vue3';

</script>

<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <!-- Navigation -->
    <nav class="bg-gray-900 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-3xl font-bold">NextGig</h1>
          </div>
          <div class="flex items-center space-x-4">
            <Link
              href="/login"
              class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
            >
              Log in
            </Link>
            <Link
              href="/register"
              class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md text-sm font-medium"
            >
              Sign up
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
      <h2 class="text-5xl font-extrabold">
        Find Your Dream Job Today
      </h2>
      <p class="mt-4 text-lg opacity-90">
        Explore thousands of job listings and connect with top employers.
      </p>
 
      <div class="mt-6 flex justify-center space-x-4">
        <Link
          href="/Jobs"
          class="bg-white text-gray-900 px-6 py-3 rounded-lg shadow-md font-medium hover:bg-gray-200 transition"
        >
          Browse Jobs
        </Link>
        <Link
          href="/contact"
          class="border border-white px-6 py-3 rounded-lg shadow-md font-medium hover:bg-white hover:text-gray-900 transition"
        >
          Contact Us
        </Link>
      </div>
    </div>

    <!-- Features Section -->
    <section class="max-w-7xl mx-auto py-16 px-6 space-y-16">
      <div class="text-center">
        <h2 class="text-3xl font-bold">Why Choose Us?</h2>
        <p class="mt-3 text-2xl">We help job seekers and employers connect efficiently.</p>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="text-center">
          <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-medium">Latest Job Listings</h3>
          <p class="mt-2 text-base">
            Access the most recent job opportunities from top companies
          </p>
        </div>

        <div class="text-center">
          <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-medium">Quick Application</h3>
          <p class="mt-2 text-base">
            Simple and fast application process for your dream job
          </p>
        </div>

        <div class="text-center">
          <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-medium">Instant Updates</h3>
          <p class="mt-2 text-base">
            Get notified about new opportunities matching your profile
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div class="text-gray-300 text-sm">
            © {{ new Date().getFullYear() }} NextGig. All rights reserved.
          </div>
          <div class="flex space-x-6">
            <Link href="/about" class="text-gray-300 hover:text-white">About</Link>
            <Link href="/contact" class="text-gray-300 hover:text-white">Contact</Link>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
