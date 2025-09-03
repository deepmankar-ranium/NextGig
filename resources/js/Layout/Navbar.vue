<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
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
</template>
