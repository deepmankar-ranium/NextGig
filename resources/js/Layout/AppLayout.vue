<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Home, Briefcase, User, Info, LogOut, Menu, X } from 'lucide-vue-next';

const isMenuOpen = ref(false);
const page = usePage();

const isEmployer = computed(() => page.props.auth.user?.role_id === 2);
const isJobSeeker = computed(() => page.props.auth.user?.role_id === 3);

const navItems = computed(() => [
  { name: 'Home', href: '/Home', icon: Home },
  { name: 'Jobs', href: '/Jobs', icon: Briefcase },
  { name: 'Profile', href: '/profile', icon: User },
  ...(isEmployer.value ? [{ name: 'Posted Jobs', href: '/posted-jobs', icon: Briefcase }] : []),
  ...(isJobSeeker.value ? [{ name: 'Applied Jobs', href: '/applied-jobs', icon: Briefcase }] : []),
  { name: 'About', href: '/about', icon: Info },
  { name: 'Chat With AI', href: '/chat', icon: Info },
]);

const currentYear = new Date().getFullYear();
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          <!-- Logo -->
          <div class="flex-shrink-0">
            <Link href="/Home" class="text-3xl font-bold text-indigo-600">NextGig</Link>
          </div>

          <!-- Desktop Navigation -->
          <nav class="hidden md:flex items-center space-x-0.5 xl:space-x-1 2xl:space-x-1.5">
            <Link
              v-for="item in navItems"
              :key="item.name"
              :href="item.href"
              class="px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors flex items-center"
              :class="{ 'bg-gray-100 text-indigo-600': $page.url.startsWith(item.href) }"
            >
              <component :is="item.icon" class="h-5 w-5 inline-block mr-1" />
              <span>{{ item.name }}</span>
            </Link>
          </nav>

          <!-- Desktop Logout -->
          <div class="hidden md:flex items-center">
            <Link
              href="/logout"
              method="post"
              as="button"
              class="ml-6 inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
            >
              <LogOut class="h-5 w-5 mr-2" />
              Logout
            </Link>
          </div>

          <!-- Mobile menu button -->
          <div class="md:hidden flex items-center">
            <button @click="isMenuOpen = !isMenuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
              <span class="sr-only">Open main menu</span>
              <Menu v-if="!isMenuOpen" class="block h-6 w-6" aria-hidden="true" />
              <X v-else class="block h-6 w-6" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div v-show="isMenuOpen" class="md:hidden border-t border-gray-200">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <Link
            v-for="item in navItems"
            :key="item.name"
            :href="item.href"
            class="flex items-center px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
            :class="{ 'bg-indigo-50 text-indigo-700': $page.url.startsWith(item.href) }"
          >
            <component :is="item.icon" class="mr-3 h-6 w-6 text-gray-500" :class="{'text-indigo-600': $page.url.startsWith(item.href)}" />
            {{ item.name }}
          </Link>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="px-2 space-y-1">
                <Link
                  href="/logout"
                  method="post"
                  as="button"
                  class="w-full text-left flex items-center px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50"
                >
                  <LogOut class="mr-3 h-6 w-6 text-gray-500" />
                  Logout
                </Link>
            </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <slot></slot>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t">
      <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-500">
          <p>&copy; {{ currentYear }} NextGig. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
