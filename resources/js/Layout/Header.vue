<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { MessageSquare, ChevronDown, User, FileText, Tag, Info, LogOut } from 'lucide-vue-next';
import { useNotificationStore } from '../store/notifications';
import Avatar from '@/Components/Chat/Avatar.vue';
import { primaryNav, isActive, ROLE_EMPLOYER, ROLE_SEEKER } from '@/utils/nav';

const page = usePage();
const store = useNotificationStore();

const user = computed(() => page.props.auth?.user ?? null);
const roleId = computed(() => user.value?.role_id);
const badges = computed(() => ({
  applications: store.applicationUpdates || null,
  inbox: store.unreadCount || null,
}));
const navItems = computed(() => (user.value ? primaryNav(roleId.value, badges.value) : []));

const menuOpen = ref(false);
const menuRef = ref(null);
const onDocClick = (e) => {
  if (menuOpen.value && menuRef.value && !menuRef.value.contains(e.target)) menuOpen.value = false;
};
const onKey = (e) => { if (e.key === 'Escape') menuOpen.value = false; };
onMounted(() => { document.addEventListener('click', onDocClick); document.addEventListener('keydown', onKey); });
onBeforeUnmount(() => { document.removeEventListener('click', onDocClick); document.removeEventListener('keydown', onKey); });

const menuItems = computed(() => [
  { name: 'Profile', href: '/profile', icon: User },
  ...(roleId.value === ROLE_SEEKER ? [{ name: 'Resume & skills', href: '/resume', icon: FileText }] : []),
  ...(roleId.value === ROLE_EMPLOYER ? [{ name: 'Tags', href: '/tags', icon: Tag }] : []),
  { name: 'About NextGig', href: '/about', icon: Info },
]);
const roleLabel = computed(() => (roleId.value === ROLE_EMPLOYER ? 'Employer' : roleId.value === ROLE_SEEKER ? 'Job seeker' : ''));
</script>

<template>
  <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
      <Link :href="user ? '/home' : '/'" class="text-2xl font-bold text-indigo-600">NextGig</Link>

      <!-- Primary tabs (desktop) -->
      <nav v-if="user" class="hidden h-full items-stretch gap-1 md:flex" aria-label="Main">
        <Link
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          class="relative flex items-center gap-2 px-3 text-sm font-medium transition-colors"
          :class="isActive($page.url, item.href) ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900'"
          :aria-current="isActive($page.url, item.href) ? 'page' : undefined"
        >
          {{ item.name }}
          <span v-if="item.badge" class="rounded-full bg-indigo-600 px-1.5 text-xs font-semibold text-white">{{ item.badge }}</span>
          <span v-if="isActive($page.url, item.href)" class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-indigo-600" />
        </Link>
      </nav>

      <div class="ml-auto flex items-center gap-2">
        <template v-if="user">
          <!-- Inbox -->
          <Link href="/messages" class="relative hidden rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800 md:block"
            :class="{ 'text-indigo-600': isActive($page.url, '/messages') }" aria-label="Inbox">
            <MessageSquare class="h-5 w-5" />
            <span v-if="badges.inbox" class="absolute -right-0.5 -top-0.5 min-w-[1.1rem] rounded-full bg-red-500 px-1 text-center text-[10px] font-bold leading-[1.1rem] text-white">
              {{ badges.inbox }}
            </span>
          </Link>

          <!-- Avatar menu -->
          <div ref="menuRef" class="relative">
            <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-haspopup="menu"
              class="flex items-center gap-1 rounded-full p-0.5 pr-1.5 hover:bg-gray-100">
              <Avatar :id="user.id" :name="user.full_name" :src="user.photo_url" size="sm" />
              <ChevronDown class="hidden h-4 w-4 text-gray-500 sm:block" />
            </button>

            <transition enter-active-class="transition duration-100" enter-from-class="opacity-0 scale-95"
              leave-active-class="transition duration-75" leave-to-class="opacity-0 scale-95">
              <div v-if="menuOpen" role="menu"
                class="absolute right-0 mt-2 w-60 origin-top-right rounded-xl border border-gray-200 bg-white py-1 shadow-lg">
                <div class="border-b border-gray-100 px-4 py-3">
                  <p class="truncate text-sm font-semibold text-gray-900">{{ user.full_name }}</p>
                  <p class="truncate text-xs text-gray-500">{{ user.email }}</p>
                  <p v-if="roleLabel" class="mt-1 text-xs font-medium text-indigo-600">{{ roleLabel }}</p>
                </div>
                <Link v-for="item in menuItems" :key="item.href" :href="item.href" role="menuitem" @click="menuOpen = false"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  <component :is="item.icon" class="h-4 w-4 text-gray-400" /> {{ item.name }}
                </Link>
                <div class="my-1 border-t border-gray-100" />
                <Link href="/logout" method="post" as="button" role="menuitem"
                  class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">
                  <LogOut class="h-4 w-4 text-gray-400" /> Log out
                </Link>
              </div>
            </transition>
          </div>
        </template>

        <template v-else>
          <Link href="/login" class="btn-ghost">Log in</Link>
          <Link href="/register" class="btn-primary">Sign up</Link>
        </template>
      </div>
    </div>
  </header>
</template>
