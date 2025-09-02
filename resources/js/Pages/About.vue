<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import { Target, Eye, Users } from 'lucide-vue-next';
</script>

<template>
  <AppLayout>
    <div class="bg-white">
      <!-- Hero Section -->
      <div class="relative bg-gray-50 py-24 sm:py-32">
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-64 h-64 bg-indigo-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-purple-100 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
            About <span class="text-indigo-600">NextGig</span>
          </h1>
          <p class="mt-6 max-w-3xl mx-auto text-xl text-gray-600">
            We're on a mission to bridge the gap between talented professionals and innovative companies, creating futures one job at a time.
          </p>
        </div>
      </div>

      <!-- Mission and Vision Section -->
      <div class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="lg:grid lg:grid-cols-2 lg:gap-16 lg:items-center">
            <div>
              <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Our Mission & Vision</h2>
              <p class="mt-4 text-lg text-gray-600">
                NextGig was founded on a simple yet powerful idea: to make the job market more transparent, accessible, and efficient for everyone involved. We believe that the right job can transform a person's life, and the right person can transform a business.
              </p>
              <dl class="mt-10 space-y-10">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                      <Target class="h-6 w-6" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <dt class="text-lg leading-6 font-medium text-gray-900">Our Mission</dt>
                    <dd class="mt-2 text-base text-gray-600">To empower professionals to build fulfilling careers and to help companies find the talent they need to succeed. We are committed to creating a platform that is intuitive, effective, and supportive.</dd>
                  </div>
                </div>
                <div class="flex">
                  <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white">
                      <Eye class="h-6 w-6" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <dt class="text-lg leading-6 font-medium text-gray-900">Our Vision</dt>
                    <dd class="mt-2 text-base text-gray-600">To become the world's most trusted and user-centric platform for career development and talent acquisition, shaping the future of work for the better.</dd>
                  </div>
                </div>
              </dl>
            </div>
            <div class="mt-10 lg:mt-0" aria-hidden="true">
                <img class="relative mx-auto rounded-lg shadow-xl" width="490" src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop" alt="People working together">
            </div>
          </div>
        </div>
      </div>

      <!-- Team Section -->
      <div class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="lg:text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Meet Our Team</h2>
            <p class="mt-4 max-w-2xl text-xl text-gray-600 lg:mx-auto">
              The passionate individuals behind NextGig's success.
            </p>
          </div>
          <div class="mt-16 flex justify-center">
            <!-- Team Member Card -->
            <div class="text-center">
              <div class="h-32 w-32 rounded-full bg-gray-300 mx-auto flex items-center justify-center">
                <Users class="h-16 w-16 text-gray-500" />
              </div>
              <h3 class="mt-6 text-xl font-medium text-gray-900">Deep Mankar</h3>
              <p class="text-base font-medium text-indigo-600">CEO & Founder</p>
            </div>

          </div>
        </div>
      </div>

      <!-- CTA Section -->
      <div class="bg-indigo-700">
        <div class="max-w-4xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                Ready to Take the Next Step?
            </h2>
            <p class="mt-4 text-lg leading-6 text-indigo-200">
                Whether you're looking for your next role or searching for top talent, we're here to help.
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <a href="/Jobs" class="inline-block bg-white text-indigo-600 font-bold rounded-lg px-8 py-3 hover:bg-indigo-50 transition-colors duration-300">
                    Browse Jobs
                </a>
                <a href="/contact" class="inline-block bg-indigo-600 text-white border border-indigo-400 font-bold rounded-lg px-8 py-3 hover:bg-indigo-500 transition-colors duration-300">
                    Contact Us
                </a>
            </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
