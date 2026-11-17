<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useNotificationStore } from '../store/notifications';
import { mobileNav, isActive } from '@/utils/nav';

const page = usePage();
const store = useNotificationStore();
const items = computed(() => mobileNav(page.props.auth?.user?.role_id, {
  applications: store.applicationUpdates || null,
  inbox: store.unreadCount || null,
}));
</script>

<template>
  <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/95 backdrop-blur md:hidden"
    style="padding-bottom: env(safe-area-inset-bottom)" aria-label="Main">
    <ul class="grid grid-cols-5">
      <li v-for="item in items" :key="item.href">
        <Link :href="item.href"
          class="relative flex h-16 flex-col items-center justify-center gap-0.5 text-[11px] font-medium"
          :class="isActive($page.url, item.href) ? 'text-indigo-600' : 'text-gray-500'"
          :aria-current="isActive($page.url, item.href) ? 'page' : undefined">
          <component :is="item.icon" class="h-5 w-5" />
          {{ item.name }}
          <span v-if="item.badge" class="absolute right-1/2 top-1 translate-x-4 min-w-[1rem] rounded-full bg-red-500 px-1 text-center text-[10px] font-bold leading-4 text-white">
            {{ item.badge }}
          </span>
        </Link>
      </li>
    </ul>
  </nav>
</template>
