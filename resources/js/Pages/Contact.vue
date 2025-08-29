<script setup>
import AppLayout from '../Layout/AppLayout.vue';
import PageContainer from '@/Components/PageContainer.vue';
</script>

<template>
  <AppLayout>
    <div class="bg-gray-50">
      <PageContainer>
        <!-- Header -->
        <div class="text-center py-16">
          <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gray-900">
            Get in <span class="text-indigo-600">Touch</span>
          </h1>
          <p class="mt-4 max-w-2xl mx-auto text-lg md:text-xl text-gray-600">
            Have a question or want to work with us? We'd love to hear from you.
          </p>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto pb-24 px-4 sm:px-6 lg:px-8">
          <div class="grid md:grid-cols-2 gap-16 items-start">
            
            <!-- Contact Information -->
            <div class="bg-white p-8 rounded-xl shadow-md">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h3>
              <p class="text-gray-600 mb-8">
                Fill up the form and our team will get back to you within 24 hours. Or, reach out to us directly through one of the channels below.
              </p>
              
              <div class="space-y-6">
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-lg font-semibold text-gray-800">Email Us</h4>
                    <p class="text-gray-600">Our team is here to help.</p>
                    <a href="mailto:hello@nextgig.com" class="text-indigo-600 hover:text-indigo-700 font-medium">hello@nextgig.com</a>
                  </div>
                </div>

                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-lg font-semibold text-gray-800">Our Office</h4>
                    <p class="text-gray-600">Come say hello at our office HQ.</p>
                    <p class="font-medium text-gray-800">123 Innovation Drive, Suite 456<br>Tech City, 78910</p>
                  </div>
                </div>

                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-lg font-semibold text-gray-800">Call Us</h4>
                    <p class="text-gray-600">Mon-Fri from 8am to 5pm.</p>
                    <a href="tel:+1-555-123-4567" class="text-indigo-600 hover:text-indigo-700 font-medium">+1 (555) 123-4567</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-white p-8 rounded-xl shadow-md">
              <form class="space-y-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                  <div class="mt-1">
                    <input type="text" name="name" id="name" autocomplete="name"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  </div>
                </div>
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                  <div class="mt-1">
                    <input id="email" name="email" type="email" autocomplete="email"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  </div>
                </div>
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                  <div class="mt-1">
                    <textarea id="message" name="message" rows="4"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                  </div>
                </div>
                <div>
                  <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-300">
                    Send Message
                  </button>
                </div>
              </form>
            </div>

          </div>
        </div>
      </PageContainer>
    </div>
  </AppLayout>
</template>
