<script setup>
import { ref,watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { HomeIcon, SearchIcon, BriefcaseIcon, UserIcon, MenuIcon, XIcon, InfoIcon, MailIcon } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';


const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

 
// Get the auth user from Inertia.js page props
const page = usePage();

const isEmployer = computed(() => page.props.auth.user?.role_id=== 2);
const isJobSeeker = computed(() => page.props.auth.user?.role_id === 3);


// Use `.value` when spreading into `navItems`
const navItems = computed(() => [
  { name: 'Home', icon: HomeIcon, href: '/Home' },
  { name: 'Jobs', icon: BriefcaseIcon, href: '/Jobs' },
  { name: 'Profile', icon: UserIcon, href: '/profile' },
  { name: 'About', icon: InfoIcon, href: '/about' },
  ...(isEmployer.value ? [{ name: 'Posted Jobs', icon: BriefcaseIcon, href: '/posted-jobs' }] : []),
  ...(isJobSeeker.value ? [{ name: 'Applied Jobs', icon: BriefcaseIcon, href: '/applied-jobs' }] : []),
  { name: 'Chat With AI', href: '/chat' },
]);


const currentYear = new Date().getFullYear();
</script>

<template>
  <div class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-gray-900 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <span class="text-xl font-bold text-gray-900 dark:text-white">NextGig</span>
          </div>
          
          <!-- Desktop Navigation -->
          <nav class="hidden md:flex space-x-8">
            <Link v-for="item in navItems" :key="item.name" :href="item.href"
               class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white flex items-center space-x-1">
              <component :is="item.icon" class="h-5 w-5" />
              <span>{{ item.name }}</span>
            </Link>
          </nav>
          
          <!-- Mobile menu button -->
          <div class="md:hidden">
            <button @click="toggleMenu" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
              <MenuIcon v-if="!isMenuOpen" class="h-6 w-6" />
              <XIcon v-else class="h-6 w-6" />
            </button>
          </div>
        </div>
      </div>
      
      <!-- Mobile Navigation -->
      <div v-show="isMenuOpen" class="md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <Link v-for="item in navItems" :key="item.name" :href="item.href"
             class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white block px-3 py-2 rounded-md text-base font-medium">
            <component :is="item.icon" class="h-5 w-5 inline-block mr-2" />
            {{ item.name }}
          </Link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow bg-gradient-to-b from-gray-50 to-white">
      <slot></slot>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="text-center md:text-left mb-4 md:mb-0">
            <p class="text-sm text-white">&copy; {{ currentYear }} NextGig. All rights reserved.</p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: trangrayY(-10px); }
  to { opacity: 1; transform: trangrayY(0); }
}

header, footer {
  animation: fadeIn 0.5s ease-out forwards;
}

.router-link-active {
  @apply text-gray-600 dark:text-gray-400;
}
</style>
