<script setup>
import { ref } from 'vue';
import { HomeIcon, SearchIcon, BriefcaseIcon, UserIcon, MenuIcon, XIcon,LockIcon} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};
const navItems = [
  { name: 'Register', icon: UserIcon, href: '/register' },
  { name: 'Log In', icon: LockIcon, href: '/login' },
];

const currentYear = new Date().getFullYear();
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <nav class="flex justify-between items-center">
          <div class="text-2xl font-bold text-gray-900">NextGig</div>
          <div class="space-x-4">
            <Link 
              v-for="link in navigationLinks"
              :key="link.href"
              :href="link.href"
              class="text-gray-600 hover:text-gray-900 transition-colors duration-200"
            >
              {{ link.label }}
            </Link>
          </div>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <slot></slot>
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow-md mt-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center text-gray-600">
          <p>&copy; {{ new Date().getFullYear() }} NextGig. All rights reserved.</p>
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