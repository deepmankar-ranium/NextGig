<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="bg-gray-50 text-gray-800 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md shadow-sm fixed w-full z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
          <div class="flex items-center">
            <Link href="/" class="text-3xl font-bold text-indigo-600">NextGig</Link>
          </div>
          <div class="hidden md:flex items-center space-x-8">
            <Link href="/Jobs" class="text-gray-600 hover:text-indigo-600 transition-colors duration-300">Browse Jobs</Link>
            <Link href="#features" class="text-gray-600 hover:text-indigo-600 transition-colors duration-300">Features</Link>
            <Link href="/contact" class="text-gray-600 hover:text-indigo-600 transition-colors duration-300">Contact</Link>
          </div>
          <div class="flex items-center space-x-4">
            <Link
              href="/login"
              class="text-gray-600 hover:text-indigo-600 px-4 py-2 rounded-md text-sm font-medium transition-colors duration-300"
            >
              Log In
            </Link>
            <Link
              href="/register"
              class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md text-sm font-medium shadow-md hover:shadow-lg transition-all duration-300"
            >
              Sign Up
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <header class="pt-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center relative">
        <div class="absolute top-0 left-0 w-64 h-64 bg-indigo-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-purple-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-8 left-20 w-64 h-64 bg-pink-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>

        <div class="relative">
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-gray-900">
                Find Your <span class="text-indigo-600">Next Opportunity</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg md:text-xl text-gray-600">
                NextGig is where talent meets opportunity. Discover thousands of jobs from top companies and find the one that's right for you.
            </p>

            <div class="mt-8 flex justify-center gap-4">
                <Link
                href="/jobs"
                class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-lg shadow-lg font-semibold hover:bg-indigo-700 transform hover:-translate-y-1 transition-all duration-300"
                >
                Browse All Jobs
                </Link>
                <Link
                href="/register?role=employer"
                class="inline-block bg-white text-indigo-600 border border-indigo-200 px-8 py-3 rounded-lg font-semibold hover:bg-indigo-50 transform hover:-translate-y-1 transition-all duration-300"
                >
                Post a Job
                </Link>
            </div>
        </div>
      </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h2 class="text-3xl font-extrabold text-gray-900">A Better Way to Find Work</h2>
          <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-600">
            We've built a platform to simplify your job search and connect you with the best opportunities.
          </p>
        </div>

        <div class="mt-16 grid md:grid-cols-3 gap-12">
          <!-- Feature 1 -->
          <div class="text-center p-8 bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 mx-auto">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <h3 class="mt-6 text-xl font-bold text-gray-900">Smart Search</h3>
            <p class="mt-2 text-gray-600">
              Our advanced search filters help you find jobs that perfectly match your skills, experience, and preferences.
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="text-center p-8 bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 mx-auto">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="mt-6 text-xl font-bold text-gray-900">One-Click Apply</h3>
            <p class="mt-2 text-gray-600">
              Apply to jobs with a single click. We streamline the process so you can focus on what matters.
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="text-center p-8 bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-600 mx-auto">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <h3 class="mt-6 text-xl font-bold text-gray-900">Instant Alerts</h3>
            <p class="mt-2 text-gray-600">
              Never miss an opportunity. Get real-time alerts for new jobs that match your profile.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-indigo-700">
        <div class="max-w-4xl mx-auto text-center py-16 px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                Ready to Find Your Next Gig?
            </h2>
            <p class="mt-4 text-lg text-indigo-100">
                Join thousands of others and take the next step in your career today.
            </p>
            <div class="mt-8">
                <Link href="/register" class="inline-block bg-white text-indigo-600 font-bold rounded-lg px-8 py-3 hover:bg-indigo-50 transition-colors duration-300">
                    Create Your Account
                </Link>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex justify-center space-x-6 md:order-2">
                <a href="#" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Facebook</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Twitter</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.71v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                    </svg>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">GitHub</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.168 6.839 9.49.5.092.682-.217.682-.482 0-.237-.009-.868-.014-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.031-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.03 1.595 1.03 2.688 0 3.848-2.338 4.695-4.566 4.942.359.308.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.001 10.001 0 0022 12c0-5.523-4.477-10-10-10z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            <div class="mt-8 md:mt-0 md:order-1">
                <p class="text-center text-base text-gray-400">&copy; {{ new Date().getFullYear() }} NextGig. All rights reserved.</p>
            </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
.animation-delay-4000 {
  animation-delay: 4s;
}
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}
</style>
